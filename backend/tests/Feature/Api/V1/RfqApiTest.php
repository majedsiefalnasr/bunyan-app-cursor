<?php

namespace Tests\Feature\Api\V1;

use App\Enums\RfqStatus;
use App\Models\Rfq;
use App\Models\RfqTarget;
use App\Models\SupplierProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RfqApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_cannot_access_rfqs(): void
    {
        $this->getJson('/api/v1/rfqs')->assertStatus(401);
    }

    public function test_customer_can_create_and_list_own_rfqs(): void
    {
        $customer = User::factory()->customer()->create();

        $create = $this->actingAs($customer)->postJson('/api/v1/rfqs', [
            'title' => 'طلب تسعير',
            'items' => [
                ['description' => 'اسمنت', 'quantity' => 10, 'unit' => 'كيس'],
            ],
        ]);

        $create->assertStatus(201)->assertJson(['success' => true]);

        $list = $this->actingAs($customer)->getJson('/api/v1/rfqs?per_page=15&sort=-created_at');
        $list->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_customer_cannot_view_other_customer_rfq(): void
    {
        $owner = User::factory()->customer()->create();
        $stranger = User::factory()->customer()->create();
        $rfq = Rfq::factory()->draft()->create(['created_by' => $owner->id]);

        $this->actingAs($stranger)->getJson("/api/v1/rfqs/{$rfq->id}")->assertStatus(403);
    }

    public function test_admin_cannot_create_rfq_read_only(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->postJson('/api/v1/rfqs', [
            'title' => 'طلب',
            'items' => [
                ['description' => 'حديد', 'quantity' => 1, 'unit' => 'طن'],
            ],
        ])->assertStatus(403);
    }

    public function test_customer_can_send_rfq_and_rate_limiter_is_per_rfq(): void
    {
        $customer = User::factory()->customer()->create();
        $rfq1 = Rfq::factory()->draft()->create(['created_by' => $customer->id, 'response_deadline' => now()->addDays(2)]);
        $rfq2 = Rfq::factory()->draft()->create(['created_by' => $customer->id, 'response_deadline' => now()->addDays(2)]);

        $this->actingAs($customer)->postJson("/api/v1/rfqs/{$rfq1->id}/send")->assertStatus(200);

        // Hit the limiter for rfq1
        for ($i = 0; $i < 6; $i++) {
            $this->actingAs($customer)->postJson("/api/v1/rfqs/{$rfq1->id}/send");
        }
        $this->actingAs($customer)->postJson("/api/v1/rfqs/{$rfq1->id}/send")->assertStatus(429);

        // rfq2 should not be affected by rfq1 limiter key
        $this->actingAs($customer)->postJson("/api/v1/rfqs/{$rfq2->id}/send")->assertStatus(200);
    }

    public function test_contractor_can_view_invited_rfq(): void
    {
        $customer = User::factory()->customer()->create();
        $rfq = Rfq::factory()->draft()->create(['created_by' => $customer->id, 'status' => RfqStatus::Quoting->value]);

        $supplierProfile = SupplierProfile::factory()->verified()->create();
        $contractor = $supplierProfile->user;

        RfqTarget::query()->create([
            'rfq_id' => $rfq->id,
            'supplier_id' => $supplierProfile->id,
            'invited_at' => now(),
        ]);

        $this->actingAs($contractor)->getJson("/api/v1/rfqs/{$rfq->id}")->assertStatus(200);
    }

    public function test_customer_can_begin_evaluation_when_quoting_and_not_twice(): void
    {
        $customer = User::factory()->customer()->create();
        $rfq = Rfq::factory()->draft()->create([
            'created_by' => $customer->id,
            'status' => RfqStatus::Quoting->value,
            'response_deadline' => now()->addDay(),
        ]);

        $this->actingAs($customer)
            ->postJson("/api/v1/rfqs/{$rfq->id}/evaluate")
            ->assertStatus(200)
            ->assertJsonPath('data.status', RfqStatus::Evaluation->value);

        $this->actingAs($customer)
            ->postJson("/api/v1/rfqs/{$rfq->id}/evaluate")
            ->assertStatus(422);
    }
}
