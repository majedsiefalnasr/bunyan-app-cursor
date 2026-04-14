<?php

namespace Tests\Unit\Enums;

use App\Enums\AnalyticsMetricKey;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ProjectRole;
use App\Enums\QuotationStatus;
use App\Enums\RfqStatus;
use App\Enums\SupplierVerificationStatus;
use Tests\TestCase;

class AdditionalEnumsTest extends TestCase
{
    public function test_values_methods_match_cases(): void
    {
        $this->assertSame(array_column(PaymentMethod::cases(), 'value'), PaymentMethod::values());
        $this->assertSame(array_column(PaymentStatus::cases(), 'value'), PaymentStatus::values());
        $this->assertSame(array_column(ProjectRole::cases(), 'value'), ProjectRole::values());
        $this->assertSame(array_column(QuotationStatus::cases(), 'value'), QuotationStatus::values());
        $this->assertSame(array_column(RfqStatus::cases(), 'value'), RfqStatus::values());
        $this->assertSame(array_column(SupplierVerificationStatus::cases(), 'value'), SupplierVerificationStatus::values());
        $this->assertSame(array_column(AnalyticsMetricKey::cases(), 'value'), AnalyticsMetricKey::values());
    }

    public function test_labels_are_non_empty(): void
    {
        foreach (PaymentMethod::cases() as $c) {
            $this->assertNotSame('', $c->label());
        }
        foreach (PaymentStatus::cases() as $c) {
            $this->assertNotSame('', $c->label());
        }
        foreach (ProjectRole::cases() as $c) {
            $this->assertNotSame('', $c->label());
        }
        foreach (QuotationStatus::cases() as $c) {
            $this->assertNotSame('', $c->label());
        }
        foreach (RfqStatus::cases() as $c) {
            $this->assertNotSame('', $c->label());
        }
        foreach (SupplierVerificationStatus::cases() as $c) {
            $this->assertNotSame('', $c->label());
        }
    }
}
