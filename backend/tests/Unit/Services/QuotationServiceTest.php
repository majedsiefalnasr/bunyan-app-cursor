<?php

namespace Tests\Unit\Services;

use App\Enums\QuotationStatus;
use App\Enums\RfqStatus;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Rfq;
use App\Models\RfqItem;
use App\Models\SupplierProfile;
use App\Models\User;
use App\Services\QuotationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class QuotationServiceTest extends TestCase
{
    use RefreshDatabase;

    private QuotationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(QuotationService::class);
    }

    public function test_upsert_requires_rfq_in_quoting_state(): void
    {
        $supplier = SupplierProfile::factory()->create();
        $user = $supplier->user;
        $rfq = Rfq::factory()->create(['status' => RfqStatus::Draft->value]);

        $this->expectException(ValidationException::class);
        $this->service->upsertForRfq($user, $rfq, ['items' => []]);
    }

    public function test_upsert_creates_then_revises_existing_quotation_and_recalculates_total(): void
    {
        $supplier = SupplierProfile::factory()->create();
        $user = $supplier->user;

        $rfq = Rfq::factory()->create([
            'status' => RfqStatus::Quoting->value,
            'response_deadline' => now()->addDay(),
        ]);

        $p1 = Product::factory()->create();
        $p2 = Product::factory()->create();

        $i1 = RfqItem::factory()->create([
            'rfq_id' => $rfq->id,
            'product_id' => $p1->id,
            'quantity' => 2,
        ]);
        $i2 = RfqItem::factory()->create([
            'rfq_id' => $rfq->id,
            'product_id' => $p2->id,
            'quantity' => 3,
        ]);

        $q1 = $this->service->upsertForRfq($user, $rfq, [
            'items' => [
                ['rfq_item_id' => $i1->id, 'unit_price' => 10],
                ['rfq_item_id' => $i2->id, 'unit_price' => 5],
            ],
            'delivery_days' => 7,
        ]);

        $this->assertSame($rfq->id, $q1->rfq_id);
        $this->assertSame($supplier->id, $q1->supplier_id);
        $this->assertSame(QuotationStatus::Submitted, $q1->status);
        $this->assertSame(35.0, (float) $q1->total_price); // 2*10 + 3*5
        $this->assertCount(2, $q1->items);

        $q2 = $this->service->upsertForRfq($user, $rfq->fresh(), [
            'items' => [
                ['rfq_item_id' => $i1->id, 'unit_price' => 20],
                ['rfq_item_id' => $i2->id, 'unit_price' => 5],
            ],
        ]);

        $this->assertSame($q1->id, $q2->id);
        $this->assertSame(55.0, (float) $q2->total_price); // 2*20 + 3*5
        $this->assertSame(QuotationStatus::Revised, $q2->status);
    }

    public function test_upsert_rejects_invalid_rfq_item_id(): void
    {
        $supplier = SupplierProfile::factory()->create();
        $user = $supplier->user;

        $rfq = Rfq::factory()->create([
            'status' => RfqStatus::Quoting->value,
            'response_deadline' => now()->addDay(),
        ]);

        $this->expectException(ValidationException::class);
        $this->service->upsertForRfq($user, $rfq, [
            'items' => [
                ['rfq_item_id' => 999999, 'unit_price' => 10],
            ],
        ]);
    }

    public function test_accept_requires_evaluation_and_awards_rfq_and_rejects_others(): void
    {
        $awarder = User::factory()->admin()->create();
        $rfq = Rfq::factory()->evaluation()->create();

        $qAccepted = Quotation::factory()->create(['rfq_id' => $rfq->id]);
        $qOther = Quotation::factory()->create(['rfq_id' => $rfq->id]);

        $updated = $this->service->accept($awarder, $rfq, $qAccepted);

        $this->assertSame(RfqStatus::Awarded, $updated->status);
        $this->assertSame($qAccepted->id, $updated->awarded_quotation_id);

        $qAccepted = $qAccepted->fresh();
        $qOther = $qOther->fresh();

        $this->assertSame(QuotationStatus::Accepted, $qAccepted->status);
        $this->assertNotSame(QuotationStatus::Accepted, $qOther->status);
    }
}
