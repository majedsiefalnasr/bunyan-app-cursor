<?php

namespace Tests\Unit\Services;

use App\Enums\EstimateItemCategory;
use App\Enums\EstimateStatus;
use App\Models\Estimate;
use App\Models\Project;
use App\Models\User;
use App\Services\EstimateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class EstimateServiceTest extends TestCase
{
    use RefreshDatabase;

    private EstimateService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(EstimateService::class);
    }

    public function test_update_estimate_blocks_changes_when_approved_unless_admin(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $estimate = Estimate::factory()->create([
            'project_id' => $project->id,
            'status' => EstimateStatus::Approved,
            'created_by' => $customer->id,
        ]);

        $this->expectException(ValidationException::class);
        $this->service->updateEstimate($customer, $estimate, ['title' => 'x']);

        $updated = $this->service->updateEstimate($admin, $estimate->fresh(), ['title' => 'ok']);
        $this->assertSame('ok', $updated->title);
    }

    public function test_submit_transition_requires_draft_and_at_least_one_item_and_authorized_role(): void
    {
        $fieldEngineer = User::factory()->create(['role' => 'field_engineer']);
        $customer = User::factory()->create(['role' => 'customer']);
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $estimate = Estimate::factory()->create([
            'project_id' => $project->id,
            'status' => EstimateStatus::Draft,
            'created_by' => $customer->id,
        ]);

        $this->expectException(ValidationException::class);
        $this->service->updateEstimate($customer, $estimate, ['status' => EstimateStatus::Submitted->value]);

        $this->service->addItem($estimate->fresh(), [
            'description_ar' => 'خرسانة',
            'description_en' => 'Concrete',
            'category' => EstimateItemCategory::Material->value,
            'quantity' => 2,
            'unit' => 'm3',
            'unit_price' => 100,
            'sort_order' => 0,
        ]);

        $this->expectException(ValidationException::class);
        $this->service->updateEstimate($fieldEngineer, $estimate->fresh(), ['status' => EstimateStatus::Submitted->value]);

        $submitted = $this->service->updateEstimate($customer, $estimate->fresh(), ['status' => EstimateStatus::Submitted->value]);
        $this->assertSame(EstimateStatus::Submitted, $submitted->status);
    }

    public function test_recalculate_updates_item_totals_and_estimate_grand_total(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $project = Project::factory()->create(['customer_id' => $customer->id]);
        $estimate = Estimate::factory()->create([
            'project_id' => $project->id,
            'status' => EstimateStatus::Draft,
            'markup_percentage' => 10,
            'created_by' => $customer->id,
        ]);

        $this->service->addItem($estimate, [
            'description_ar' => 'مواد',
            'description_en' => 'Materials',
            'category' => EstimateItemCategory::Material->value,
            'quantity' => 2,
            'unit' => 'u',
            'unit_price' => 50,
        ]);
        $this->service->addItem($estimate, [
            'description_ar' => 'عمالة',
            'description_en' => 'Labor',
            'category' => EstimateItemCategory::Labor->value,
            'quantity' => 1,
            'unit' => 'u',
            'unit_price' => 40,
        ]);

        $recalc = $this->service->recalculate($estimate->fresh());
        $this->assertSame(100.0, (float) $recalc->total_materials);
        $this->assertSame(40.0, (float) $recalc->total_labor);
        $this->assertSame(0.0, (float) $recalc->total_overhead);
        $this->assertSame(154.0, (float) $recalc->grand_total);
    }

    public function test_compare_rejects_ids_not_in_project(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $projectA = Project::factory()->create(['customer_id' => $customer->id]);
        $projectB = Project::factory()->create(['customer_id' => $customer->id]);

        $a = Estimate::factory()->create(['project_id' => $projectA->id, 'created_by' => $customer->id]);
        $b = Estimate::factory()->create(['project_id' => $projectB->id, 'created_by' => $customer->id]);

        $this->expectException(ValidationException::class);
        $this->service->compare($projectA, [$a->id, $b->id]);
    }
}
