<?php

namespace Tests\Unit\Enums;

use App\Enums\ApprovalStatus;
use App\Enums\DocumentCategory;
use App\Enums\EstimateItemCategory;
use App\Enums\EstimateStatus;
use App\Enums\InvoiceStatus;
use App\Enums\NotificationType;
use App\Enums\OrderStatus;
use App\Enums\PaymentAttemptStatus;
use App\Enums\PaymentAttemptType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PhaseStatus;
use App\Enums\ProjectRole;
use App\Enums\ProjectStatus;
use App\Enums\QuotationStatus;
use App\Enums\ReportType;
use App\Enums\RfqStatus;
use App\Enums\SupplierVerificationStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Enums\UserRole;
use App\Enums\WorkflowType;
use PHPUnit\Framework\TestCase;

/**
 * Executes every {@see \BackedEnum} presentation branch (label / values) so line
 * coverage reflects real runtime paths, not only values used in feature tests.
 */
class EnumPresentationCoverageTest extends TestCase
{
    public function test_all_label_backed_enums_return_non_empty_labels(): void
    {
        $enums = [
            OrderStatus::class,
            TransactionType::class,
            TransactionStatus::class,
            WorkflowType::class,
            ApprovalStatus::class,
            ReportType::class,
            RfqStatus::class,
            QuotationStatus::class,
            PaymentStatus::class,
            InvoiceStatus::class,
            PaymentMethod::class,
            PhaseStatus::class,
            ProjectRole::class,
            SupplierVerificationStatus::class,
            UserRole::class,
            ProjectStatus::class,
            TaskStatus::class,
        ];

        foreach ($enums as $enumClass) {
            foreach ($enumClass::cases() as $case) {
                $this->assertNotSame(
                    '',
                    $case->label(),
                    sprintf('%s::%s', $enumClass, $case->name),
                );
            }
        }
    }

    public function test_all_static_values_enums_align_with_cases(): void
    {
        $enums = [
            NotificationType::class,
            DocumentCategory::class,
            EstimateStatus::class,
            PaymentAttemptStatus::class,
            PaymentAttemptType::class,
            TaskPriority::class,
            EstimateItemCategory::class,
        ];

        foreach ($enums as $enumClass) {
            $values = $enumClass::values();
            $this->assertSame(
                count($enumClass::cases()),
                count($values),
                $enumClass,
            );
        }
    }
}
