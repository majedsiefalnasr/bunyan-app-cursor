<?php

namespace Tests\Unit\Enums;

use App\Enums\ActivityLogAction;
use App\Enums\ApprovalStatus;
use App\Enums\OrderStatus;
use App\Enums\ReportType;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Enums\WorkflowType;
use PHPUnit\Framework\TestCase;

class OtherEnumsTest extends TestCase
{
    public function test_order_status_has_eight_cases(): void
    {
        $this->assertCount(8, OrderStatus::cases());
        $this->assertSame('pending', OrderStatus::Pending->value);
        $this->assertSame('confirmed', OrderStatus::Confirmed->value);
        $this->assertSame('delivered', OrderStatus::Delivered->value);
        $this->assertSame('في الانتظار', OrderStatus::Pending->label());
        $this->assertCount(8, OrderStatus::values());
    }

    public function test_transaction_type_has_four_cases(): void
    {
        $this->assertCount(4, TransactionType::cases());
        $this->assertSame('payment', TransactionType::Payment->value);
        $this->assertSame('دفع', TransactionType::Payment->label());
        $this->assertCount(4, TransactionType::values());
    }

    public function test_transaction_status_has_four_cases(): void
    {
        $this->assertCount(4, TransactionStatus::cases());
        $this->assertSame('pending', TransactionStatus::Pending->value);
        $this->assertSame('completed', TransactionStatus::Completed->value);
        $this->assertSame('مكتملة', TransactionStatus::Completed->label());
        $this->assertCount(4, TransactionStatus::values());
    }

    public function test_workflow_type_has_three_cases(): void
    {
        $this->assertCount(3, WorkflowType::cases());
        $this->assertSame('project', WorkflowType::Project->value);
        $this->assertSame('phase', WorkflowType::Phase->value);
        $this->assertSame('task', WorkflowType::Task->value);
        $this->assertSame('مشروع', WorkflowType::Project->label());
        $this->assertCount(3, WorkflowType::values());
    }

    public function test_approval_status_has_three_cases(): void
    {
        $this->assertCount(3, ApprovalStatus::cases());
        $this->assertSame('pending', ApprovalStatus::Pending->value);
        $this->assertSame('approved', ApprovalStatus::Approved->value);
        $this->assertSame('rejected', ApprovalStatus::Rejected->value);
        $this->assertSame('معتمد', ApprovalStatus::Approved->label());
        $this->assertCount(3, ApprovalStatus::values());
    }

    public function test_report_type_has_four_cases(): void
    {
        $this->assertCount(4, ReportType::cases());
        $this->assertSame('progress', ReportType::Progress->value);
        $this->assertSame('inspection', ReportType::Inspection->value);
        $this->assertSame('تقرير تقدم', ReportType::Progress->label());
        $this->assertCount(4, ReportType::values());
    }

    public function test_all_enums_try_from_invalid_return_null(): void
    {
        $this->assertNull(OrderStatus::tryFrom('invalid'));
        $this->assertNull(TransactionType::tryFrom('invalid'));
        $this->assertNull(TransactionStatus::tryFrom('invalid'));
        $this->assertNull(WorkflowType::tryFrom('invalid'));
        $this->assertNull(ApprovalStatus::tryFrom('invalid'));
        $this->assertNull(ReportType::tryFrom('invalid'));
        $this->assertNull(ActivityLogAction::tryFrom('invalid'));
    }

    public function test_activity_log_action_has_five_cases(): void
    {
        $this->assertCount(5, ActivityLogAction::cases());
        $this->assertSame('created', ActivityLogAction::Created->value);
        $this->assertSame('exported', ActivityLogAction::Exported->value);
    }
}
