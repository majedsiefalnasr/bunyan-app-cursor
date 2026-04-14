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
use Symfony\Component\HttpFoundation\StreamedResponse;
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

    public function test_approve_and_reject_require_submitted_and_authorized_role(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $admin = User::factory()->create(['role' => 'admin']);
        $contractor = User::factory()->create(['role' => 'contractor']);
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $estimate = Estimate::factory()->create([
            'project_id' => $project->id,
            'status' => EstimateStatus::Draft,
            'markup_percentage' => 0,
            'created_by' => $customer->id,
        ]);

        $this->service->addItem($estimate->fresh(), [
            'description_ar' => 'بند',
            'description_en' => 'Item',
            'category' => EstimateItemCategory::Material->value,
            'quantity' => 1,
            'unit' => 'u',
            'unit_price' => 10,
        ]);

        $estimate = $this->service->updateEstimate($customer, $estimate->fresh(), ['status' => EstimateStatus::Submitted->value]);

        $this->expectException(ValidationException::class);
        $this->service->approve($contractor, $estimate);

        $approved = $this->service->approve($admin, $estimate->fresh());
        $this->assertSame(EstimateStatus::Approved, $approved->status);

        $estimate2 = Estimate::factory()->create([
            'project_id' => $project->id,
            'status' => EstimateStatus::Submitted,
            'created_by' => $customer->id,
        ]);

        $rejected = $this->service->reject($admin, $estimate2);
        $this->assertSame(EstimateStatus::Rejected, $rejected->status);
        $this->assertNull($rejected->approved_by);
        $this->assertNull($rejected->approved_at);
    }

    public function test_item_update_recalculates_total_and_delete_validates_estimate_ownership(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $estimateA = Estimate::factory()->create([
            'project_id' => $project->id,
            'status' => EstimateStatus::Draft,
            'created_by' => $customer->id,
        ]);
        $estimateB = Estimate::factory()->create([
            'project_id' => $project->id,
            'status' => EstimateStatus::Draft,
            'created_by' => $customer->id,
        ]);

        $item = $this->service->addItem($estimateA, [
            'description_ar' => 'مواد',
            'description_en' => 'Materials',
            'category' => EstimateItemCategory::Material->value,
            'quantity' => 2,
            'unit' => 'u',
            'unit_price' => 50,
        ]);

        $updated = $this->service->updateItem($estimateA, $item->fresh(), [
            'quantity' => 3,
            'unit_price' => 20,
        ]);
        $this->assertSame(60.0, (float) $updated->total_price);

        $this->expectException(ValidationException::class);
        $this->service->deleteItem($estimateB, $item->fresh());

        $this->service->deleteItem($estimateA, $item->fresh());
        $this->assertDatabaseMissing('estimate_items', ['id' => $item->id]);
    }

    public function test_export_csv_stream_includes_bom_and_headers(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $project = Project::factory()->create(['customer_id' => $customer->id]);
        $estimate = Estimate::factory()->create([
            'project_id' => $project->id,
            'status' => EstimateStatus::Draft,
            'created_by' => $customer->id,
        ]);

        $this->service->addItem($estimate, [
            'description_ar' => 'خرسانة',
            'description_en' => 'Concrete',
            'category' => EstimateItemCategory::Material->value,
            'quantity' => 1,
            'unit' => 'm3',
            'unit_price' => 100,
        ]);

        $response = $this->service->exportCsvStream($estimate->fresh());
        $this->assertInstanceOf(StreamedResponse::class, $response);

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertIsString($content);
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('description_ar', $content);
        $this->assertStringContainsString('خرسانة', $content);
    }
}
