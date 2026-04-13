<?php

namespace Tests\Unit\Services;

use App\Enums\QuotationStatus;
use App\Enums\RfqStatus;
use App\Models\Quotation;
use App\Models\Rfq;
use App\Models\User;
use App\Repositories\QuotationRepository;
use App\Services\QuotationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationServiceAcceptTest extends TestCase
{
    use RefreshDatabase;

    public function test_accept_rolls_back_when_reject_step_fails(): void
    {
        $customer = User::factory()->customer()->create();
        $rfq = Rfq::factory()->evaluation()->create(['created_by' => $customer->id]);

        $q1 = Quotation::factory()->create([
            'rfq_id' => $rfq->id,
            'status' => QuotationStatus::Submitted->value,
        ]);

        $this->app->forgetInstance(QuotationService::class);
        $this->app->instance(
            QuotationRepository::class,
            new class extends QuotationRepository
            {
                public function rejectAllForRfqExcept(int $rfqId, int $acceptedQuotationId): void
                {
                    throw new \RuntimeException('simulated_failure');
                }
            },
        );

        $service = app(QuotationService::class);

        try {
            $service->accept($customer, $rfq->fresh(), $q1->fresh());
            $this->fail('Expected RuntimeException');
        } catch (\RuntimeException $e) {
            $this->assertSame('simulated_failure', $e->getMessage());
        }

        $this->assertDatabaseHas('quotations', [
            'id' => $q1->id,
            'status' => QuotationStatus::Submitted->value,
        ]);
        $this->assertDatabaseHas('rfqs', [
            'id' => $rfq->id,
            'status' => RfqStatus::Evaluation->value,
        ]);
    }
}
