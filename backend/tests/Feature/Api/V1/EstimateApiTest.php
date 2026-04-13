<?php

namespace Tests\Feature\Api\V1;

use App\Enums\EstimateStatus;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\Product;
use App\Models\Project;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstimateApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_list_estimates_for_own_project(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $project = Project::factory()->create(['customer_id' => $user->id]);
        Estimate::factory()->count(2)->create([
            'project_id' => $project->id,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->getJson("/api/v1/projects/{$project->id}/estimates");

        $response->assertStatus(200)->assertJson(['success' => true]);
        $payload = $response->json('data');
        $items = is_array($payload) && array_key_exists('data', $payload) ? $payload['data'] : $payload;
        $this->assertIsArray($items);
        $this->assertCount(2, $items);
    }

    public function test_stranger_cannot_list_estimates(): void
    {
        $owner = User::factory()->create(['role' => 'customer']);
        $stranger = User::factory()->create(['role' => 'customer']);
        $project = Project::factory()->create(['customer_id' => $owner->id]);

        $response = $this->actingAs($stranger)->getJson("/api/v1/projects/{$project->id}/estimates");

        $response->assertStatus(403);
    }

    public function test_field_engineer_can_read_but_not_create_estimate(): void
    {
        $engineer = User::factory()->create(['role' => 'field_engineer']);
        $project = Project::factory()->create();
        Report::factory()->create([
            'project_id' => $project->id,
            'created_by' => $engineer->id,
            'phase_id' => null,
            'task_id' => null,
        ]);

        $this->actingAs($engineer)
            ->getJson("/api/v1/projects/{$project->id}/estimates")
            ->assertStatus(200);

        $this->actingAs($engineer)
            ->postJson("/api/v1/projects/{$project->id}/estimates", [
                'title' => 'تقدير',
            ])
            ->assertStatus(403);
    }

    public function test_contractor_can_create_item_calculate_and_export_csv(): void
    {
        $user = User::factory()->create(['role' => 'contractor']);
        $project = Project::factory()->create(['contractor_id' => $user->id]);

        $create = $this->actingAs($user)->postJson("/api/v1/projects/{$project->id}/estimates", [
            'title' => 'عرض أسعار',
            'markup_percentage' => 10,
        ]);
        $create->assertStatus(201);
        $estimateId = (int) $create->json('data.id');

        $this->actingAs($user)->postJson("/api/v1/estimates/{$estimateId}/items", [
            'category' => 'material',
            'quantity' => 2,
            'unit' => 'm2',
            'unit_price' => 100,
            'description_ar' => 'بلاط',
        ])->assertStatus(201);

        $this->actingAs($user)->postJson("/api/v1/estimates/{$estimateId}/calculate")
            ->assertStatus(200)
            ->assertJsonPath('data.grand_total', '220.00');

        $csv = $this->actingAs($user)->get("/api/v1/estimates/{$estimateId}/export");
        $csv->assertStatus(200);
        $raw = $csv->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $raw);
        $this->assertStringContainsString('description_ar', $raw);
    }

    public function test_compare_requires_same_project(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $p1 = Project::factory()->create(['customer_id' => $user->id]);
        $p2 = Project::factory()->create(['customer_id' => $user->id]);
        $e1 = Estimate::factory()->create(['project_id' => $p1->id, 'created_by' => $user->id]);
        $e2 = Estimate::factory()->create(['project_id' => $p2->id, 'created_by' => $user->id]);

        $this->actingAs($user)
            ->getJson("/api/v1/projects/{$p1->id}/estimates/compare?ids={$e1->id},{$e2->id}")
            ->assertStatus(422);
    }

    public function test_customer_can_approve_submitted_estimate(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $contractor = User::factory()->create(['role' => 'contractor']);
        $project = Project::factory()->create([
            'customer_id' => $customer->id,
            'contractor_id' => $contractor->id,
        ]);
        $estimate = Estimate::factory()->create([
            'project_id' => $project->id,
            'created_by' => $contractor->id,
            'status' => EstimateStatus::Draft,
        ]);
        EstimateItem::factory()->create(['estimate_id' => $estimate->id]);

        $this->actingAs($contractor)->putJson("/api/v1/estimates/{$estimate->id}", [
            'status' => 'submitted',
        ])->assertStatus(200);

        $this->actingAs($customer)->postJson("/api/v1/estimates/{$estimate->id}/approve")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'approved');
    }

    public function test_admin_boq_template_crud(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $store = $this->actingAs($admin)->postJson('/api/v1/admin/boq-templates', [
            'name_ar' => 'قالب',
            'name_en' => 'Template',
            'items_json' => [['line' => 1]],
        ]);
        $store->assertStatus(201);
        $id = (int) $store->json('data.id');

        $this->actingAs($admin)->getJson("/api/v1/admin/boq-templates/{$id}")
            ->assertStatus(200);

        $this->actingAs($admin)->putJson("/api/v1/admin/boq-templates/{$id}", [
            'name_en' => 'Updated',
        ])->assertStatus(200)->assertJsonPath('data.name_en', 'Updated');

        $this->actingAs($admin)->deleteJson("/api/v1/admin/boq-templates/{$id}")
            ->assertStatus(200);
    }

    public function test_non_admin_cannot_access_boq_templates(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $this->actingAs($user)->getJson('/api/v1/admin/boq-templates')->assertStatus(403);
    }

    public function test_line_item_can_reference_product(): void
    {
        $user = User::factory()->create(['role' => 'contractor']);
        $project = Project::factory()->create(['contractor_id' => $user->id]);
        $product = Product::factory()->create();
        $estimate = Estimate::factory()->create([
            'project_id' => $project->id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)->postJson("/api/v1/estimates/{$estimate->id}/items", [
            'category' => 'material',
            'quantity' => 1,
            'unit' => 'unit',
            'unit_price' => 50,
            'product_id' => $product->id,
        ])->assertStatus(201)->assertJsonPath('data.product_id', $product->id);
    }
}
