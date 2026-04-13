<?php

namespace Tests\Feature\Api\V1;

use App\Enums\QuotationStatus;
use App\Enums\RfqStatus;
use App\Models\Quotation;
use App\Models\Rfq;
use App\Models\RfqItem;
use App\Models\RfqTarget;
use App\Models\SupplierProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_contractor_can_upsert_quotation_and_rate_limiter_is_per_rfq(): void
    {
        $customer = User::factory()->customer()->create();
        $rfq1 = Rfq::factory()->draft()->create([
            'created_by' => $customer->id,
            'status' => RfqStatus::Quoting->value,
            'response_deadline' => now()->addDay(),
        ]);
        $rfq2 = Rfq::factory()->draft()->create([
            'created_by' => $customer->id,
            'status' => RfqStatus::Quoting->value,
            'response_deadline' => now()->addDay(),
        ]);

        $item1 = RfqItem::factory()->create(['rfq_id' => $rfq1->id, 'product_id' => null, 'quantity' => 2, 'unit' => 'قطعة']);
        $item2 = RfqItem::factory()->create(['rfq_id' => $rfq2->id, 'product_id' => null, 'quantity' => 2, 'unit' => 'قطعة']);

        $supplierProfile = SupplierProfile::factory()->verified()->create();
        $contractor = $supplierProfile->user;

        RfqTarget::query()->create(['rfq_id' => $rfq1->id, 'supplier_id' => $supplierProfile->id, 'invited_at' => now()]);
        RfqTarget::query()->create(['rfq_id' => $rfq2->id, 'supplier_id' => $supplierProfile->id, 'invited_at' => now()]);

        $payload1 = [
            'items' => [
                ['rfq_item_id' => $item1->id, 'unit_price' => 10],
            ],
        ];

        $this->actingAs($contractor)->postJson("/api/v1/rfqs/{$rfq1->id}/quotations", $payload1)->assertStatus(201);

        // Exhaust limiter for rfq1
        for ($i = 0; $i < 12; $i++) {
            $this->actingAs($contractor)->postJson("/api/v1/rfqs/{$rfq1->id}/quotations", $payload1);
        }
        $this->actingAs($contractor)->postJson("/api/v1/rfqs/{$rfq1->id}/quotations", $payload1)->assertStatus(429);

        // rfq2 should still work
        $payload2 = [
            'items' => [
                ['rfq_item_id' => $item2->id, 'unit_price' => 10],
            ],
        ];
        $this->actingAs($contractor)->postJson("/api/v1/rfqs/{$rfq2->id}/quotations", $payload2)->assertStatus(201);
    }

    public function test_submit_after_deadline_is_rejected(): void
    {
        $customer = User::factory()->customer()->create();
        $rfq = Rfq::factory()->draft()->create([
            'created_by' => $customer->id,
            'status' => RfqStatus::Quoting->value,
            'response_deadline' => now()->subMinute(),
        ]);
        $item = RfqItem::factory()->create(['rfq_id' => $rfq->id, 'product_id' => null, 'quantity' => 2, 'unit' => 'قطعة']);
        $supplierProfile = SupplierProfile::factory()->verified()->create();
        $contractor = $supplierProfile->user;
        RfqTarget::query()->create(['rfq_id' => $rfq->id, 'supplier_id' => $supplierProfile->id, 'invited_at' => now()]);

        $this->actingAs($contractor)->postJson("/api/v1/rfqs/{$rfq->id}/quotations", [
            'items' => [['rfq_item_id' => $item->id, 'unit_price' => 10]],
        ])->assertStatus(422);
    }

    public function test_mismatched_rfq_quotation_ids_are_not_accepted(): void
    {
        $customer = User::factory()->customer()->create();
        $rfqA = Rfq::factory()->evaluation()->create(['created_by' => $customer->id]);
        $rfqB = Rfq::factory()->evaluation()->create(['created_by' => $customer->id]);

        $supplierProfile = SupplierProfile::factory()->verified()->create();
        $quoteOnB = Quotation::factory()->create(['rfq_id' => $rfqB->id, 'supplier_id' => $supplierProfile->id]);

        $this->actingAs($customer)
            ->putJson("/api/v1/rfqs/{$rfqA->id}/quotations/{$quoteOnB->id}/accept")
            ->assertStatus(404);
    }

    public function test_accept_awards_one_and_rejects_others_atomically(): void
    {
        $customer = User::factory()->customer()->create();
        $rfq = Rfq::factory()->evaluation()->create(['created_by' => $customer->id]);

        $supplierA = SupplierProfile::factory()->verified()->create();
        $supplierB = SupplierProfile::factory()->verified()->create();

        $q1 = Quotation::factory()->create(['rfq_id' => $rfq->id, 'supplier_id' => $supplierA->id, 'status' => QuotationStatus::Submitted->value]);
        $q2 = Quotation::factory()->create(['rfq_id' => $rfq->id, 'supplier_id' => $supplierB->id, 'status' => QuotationStatus::Submitted->value]);

        $this->actingAs($customer)
            ->putJson("/api/v1/rfqs/{$rfq->id}/quotations/{$q1->id}/accept")
            ->assertStatus(200);

        $this->assertDatabaseHas('rfqs', [
            'id' => $rfq->id,
            'status' => RfqStatus::Awarded->value,
            'awarded_quotation_id' => $q1->id,
        ]);

        $this->assertDatabaseHas('quotations', ['id' => $q1->id, 'status' => QuotationStatus::Accepted->value]);
        $this->assertDatabaseHas('quotations', ['id' => $q2->id, 'status' => QuotationStatus::Rejected->value]);
    }

    public function test_accept_after_evaluate_from_quoting_end_to_end(): void
    {
        $customer = User::factory()->customer()->create();
        $rfq = Rfq::factory()->draft()->create([
            'created_by' => $customer->id,
            'status' => RfqStatus::Quoting->value,
            'response_deadline' => now()->addDay(),
        ]);

        $item = RfqItem::factory()->create(['rfq_id' => $rfq->id, 'product_id' => null, 'quantity' => 2, 'unit' => 'قطعة']);
        $supplierProfile = SupplierProfile::factory()->verified()->create();
        $contractor = $supplierProfile->user;
        RfqTarget::query()->create(['rfq_id' => $rfq->id, 'supplier_id' => $supplierProfile->id, 'invited_at' => now()]);

        $this->actingAs($contractor)->postJson("/api/v1/rfqs/{$rfq->id}/quotations", [
            'items' => [['rfq_item_id' => $item->id, 'unit_price' => 10]],
        ])->assertStatus(201);

        $this->actingAs($customer)->postJson("/api/v1/rfqs/{$rfq->id}/evaluate")->assertStatus(200);

        $quotationId = (int) Quotation::query()->where('rfq_id', $rfq->id)->value('id');

        $this->actingAs($customer)
            ->putJson("/api/v1/rfqs/{$rfq->id}/quotations/{$quotationId}/accept")
            ->assertStatus(200);

        $this->assertDatabaseHas('rfqs', [
            'id' => $rfq->id,
            'status' => RfqStatus::Awarded->value,
            'awarded_quotation_id' => $quotationId,
        ]);
    }
}
