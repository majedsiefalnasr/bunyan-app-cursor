<?php

namespace Tests\Unit\Policies;

use App\Enums\DocumentCategory;
use App\Enums\InvoiceStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\QuotationStatus;
use App\Enums\WorkflowApprovalAction;
use App\Enums\WorkflowInstanceStatus;
use App\Enums\WorkflowType;
use App\Models\BoqTemplate;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Document;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\Media;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Phase;
use App\Models\Product;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\Report;
use App\Models\Rfq;
use App\Models\RfqTarget;
use App\Models\SupplierProfile;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WorkflowApproval;
use App\Models\WorkflowConfiguration;
use App\Models\WorkflowInstance;
use App\Policies\BoqTemplatePolicy;
use App\Policies\CategoryPolicy;
use App\Policies\ConversationPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\EstimatePolicy;
use App\Policies\InvoicePolicy;
use App\Policies\MediaPolicy;
use App\Policies\OrderPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PhasePolicy;
use App\Policies\ProductPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\QuotationPolicy;
use App\Policies\ReportPolicy;
use App\Policies\RfqPolicy;
use App\Policies\SupplierProfilePolicy;
use App\Policies\TaskPolicy;
use App\Policies\TransactionPolicy;
use App\Policies\UserPolicy;
use App\Policies\WorkflowApprovalPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationPoliciesTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_policy_matrix(): void
    {
        $policy = new ProjectPolicy;
        $customer = User::factory()->customer()->create();
        $contractor = User::factory()->contractor()->create();
        $architect = User::factory()->supervisingArchitect()->create();
        $admin = User::factory()->admin()->create();
        $other = User::factory()->customer()->create();

        $project = Project::factory()->create([
            'customer_id' => $customer->id,
            'contractor_id' => $contractor->id,
            'supervising_architect_id' => $architect->id,
        ]);

        $this->assertTrue($policy->viewAny($customer));
        $this->assertTrue($policy->view($customer, $project));
        $this->assertTrue($policy->view($contractor, $project));
        $this->assertTrue($policy->view($architect, $project));
        $this->assertFalse($policy->view($other, $project));
        $this->assertTrue($policy->view($admin, $project));

        $this->assertTrue($policy->create($customer));
        $this->assertFalse($policy->create($contractor));
        $this->assertTrue($policy->create($admin));

        $this->assertTrue($policy->update($customer, $project));
        $this->assertTrue($policy->update($contractor, $project));
        $this->assertTrue($policy->update($admin, $project));

        $this->assertTrue($policy->delete($customer, $project));
        $this->assertTrue($policy->delete($admin, $project));

        $this->assertTrue($policy->approve($architect, $project));
        $this->assertTrue($policy->approve($admin, $project));
        $this->assertFalse($policy->approve($customer, $project));

        $this->assertTrue($policy->restore($customer, $project));
        $this->assertTrue($policy->forceDelete($admin, $project));
        $this->assertFalse($policy->forceDelete($customer, $project));
    }

    public function test_phase_policy_matrix(): void
    {
        $policy = new PhasePolicy;
        $customer = User::factory()->customer()->create();
        $contractor = User::factory()->contractor()->create();
        $architect = User::factory()->supervisingArchitect()->create();
        $admin = User::factory()->admin()->create();
        $field = User::factory()->fieldEngineer()->create();

        $project = Project::factory()->create([
            'customer_id' => $customer->id,
            'contractor_id' => $contractor->id,
            'supervising_architect_id' => $architect->id,
        ]);
        $phase = Phase::factory()->create(['project_id' => $project->id]);

        $this->assertTrue($policy->viewAny($customer));
        $this->assertTrue($policy->view($customer, $phase));
        $this->assertTrue($policy->view($contractor, $phase));
        $this->assertFalse($policy->view($field, $phase));
        $this->assertTrue($policy->view($admin, $phase));

        $this->assertTrue($policy->create($customer));
        $this->assertTrue($policy->create($contractor));
        $this->assertFalse($policy->create($field));

        $this->assertTrue($policy->update($customer, $phase));
        $this->assertTrue($policy->update($contractor, $phase));
        $this->assertFalse($policy->update($field, $phase));

        $this->assertTrue($policy->delete($customer, $phase));
        $this->assertTrue($policy->delete($architect, $phase));
        $this->assertFalse($policy->delete($contractor, $phase));
        $this->assertTrue($policy->restore($customer, $phase));
        $this->assertTrue($policy->forceDelete($admin, $phase));
        $this->assertFalse($policy->forceDelete($customer, $phase));
    }

    public function test_task_policy_matrix(): void
    {
        $policy = new TaskPolicy;
        $customer = User::factory()->customer()->create();
        $contractor = User::factory()->contractor()->create();
        $architect = User::factory()->supervisingArchitect()->create();
        $assignee = User::factory()->fieldEngineer()->create();
        $admin = User::factory()->admin()->create();

        $project = Project::factory()->create([
            'customer_id' => $customer->id,
            'contractor_id' => $contractor->id,
            'supervising_architect_id' => $architect->id,
        ]);
        $phase = Phase::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->create([
            'phase_id' => $phase->id,
            'assigned_to' => $assignee->id,
        ]);

        $this->assertTrue($policy->view($assignee, $task));
        $this->assertTrue($policy->view($architect, $task));
        $this->assertTrue($policy->view($admin, $task));

        $this->assertTrue($policy->create($contractor, $phase));
        $this->assertTrue($policy->create($architect, $phase));
        $this->assertFalse($policy->create($customer, $phase));

        $this->assertTrue($policy->update($contractor, $task));
        $this->assertTrue($policy->update($architect, $task));
        $this->assertTrue($policy->update($assignee, $task));
        $this->assertFalse($policy->update($customer, $task));

        $this->assertTrue($policy->delete($contractor, $task));
        $this->assertTrue($policy->delete($architect, $task));
        $this->assertTrue($policy->restore($contractor, $task));
        $this->assertTrue($policy->forceDelete($admin, $task));
    }

    public function test_report_policy_matrix(): void
    {
        $policy = new ReportPolicy;
        $customer = User::factory()->customer()->create();
        $architect = User::factory()->supervisingArchitect()->create();
        $author = User::factory()->fieldEngineer()->create();
        $admin = User::factory()->admin()->create();

        $project = Project::factory()->create([
            'customer_id' => $customer->id,
            'supervising_architect_id' => $architect->id,
        ]);
        $report = Report::factory()->create([
            'project_id' => $project->id,
            'created_by' => $author->id,
        ]);

        $this->assertTrue($policy->view($author, $report));
        $this->assertTrue($policy->view($customer, $report));
        $this->assertTrue($policy->view($architect, $report));
        $this->assertTrue($policy->view($admin, $report));

        $this->assertTrue($policy->create($author));
        $this->assertTrue($policy->create($architect));
        $this->assertFalse($policy->create($customer));

        $this->assertTrue($policy->update($author, $report));
        $this->assertTrue($policy->update($admin, $report));
        $this->assertFalse($policy->update($customer, $report));

        $this->assertTrue($policy->delete($author, $report));
        $this->assertTrue($policy->delete($architect, $report));
        $this->assertTrue($policy->restore($author, $report));
        $this->assertTrue($policy->forceDelete($admin, $report));
    }

    public function test_order_policy_matrix(): void
    {
        $policy = new OrderPolicy;
        $customer = User::factory()->customer()->create();
        $other = User::factory()->customer()->create();
        $admin = User::factory()->admin()->create();

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => OrderStatus::Pending,
        ]);

        $this->assertTrue($policy->viewAny($customer));
        $this->assertTrue($policy->viewAny($admin));

        $this->assertTrue($policy->view($customer, $order));
        $this->assertFalse($policy->view($other, $order));
        $this->assertTrue($policy->view($admin, $order));

        $this->assertTrue($policy->create($customer));
        $this->assertTrue($policy->create(User::factory()->contractor()->create()));
        $this->assertFalse($policy->create(User::factory()->fieldEngineer()->create()));

        $this->assertFalse($policy->update($customer, $order));
        $this->assertFalse($policy->update($other, $order));
        $this->assertTrue($policy->update($admin, $order));

        $this->assertTrue($policy->delete($customer, $order));

        $processing = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => OrderStatus::Processing,
        ]);
        $this->assertFalse($policy->delete($customer, $processing));

        $this->assertTrue($policy->restore($customer, $order));
        $this->assertTrue($policy->forceDelete($admin, $order));
    }

    public function test_order_policy_contractor_supplier_confirm_cancel_and_status(): void
    {
        $policy = new OrderPolicy;
        $customer = User::factory()->customer()->create();
        $supplierProfile = SupplierProfile::factory()->verified()->create();
        $contractor = $supplierProfile->user;

        $orderForSupplier = Order::factory()->create([
            'customer_id' => $customer->id,
            'supplier_id' => $supplierProfile->id,
            'status' => OrderStatus::Processing,
        ]);
        $this->assertTrue($policy->view($contractor, $orderForSupplier));

        $otherSupplier = SupplierProfile::factory()->verified()->create();
        $orderOtherSupplier = Order::factory()->create([
            'customer_id' => $customer->id,
            'supplier_id' => $otherSupplier->id,
            'status' => OrderStatus::Processing,
        ]);
        $this->assertFalse($policy->view($contractor, $orderOtherSupplier));

        $pending = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => OrderStatus::Pending,
        ]);
        $this->assertTrue($policy->confirm($customer, $pending));
        $this->assertFalse($policy->confirm($customer, $orderForSupplier));

        $admin = User::factory()->admin()->create();
        $this->assertTrue($policy->confirm($admin, $pending));

        $confirmed = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => OrderStatus::Confirmed,
        ]);
        $this->assertTrue($policy->cancel($customer, $confirmed));

        $completed = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => OrderStatus::Completed,
        ]);
        $this->assertFalse($policy->cancel($customer, $completed));

        $this->assertTrue($policy->transitionStatus($admin, $pending));
        $this->assertFalse($policy->transitionStatus($customer, $pending));
    }

    public function test_category_policy_matrix(): void
    {
        $policy = new CategoryPolicy;
        $customer = User::factory()->customer()->create();
        $admin = User::factory()->admin()->create();
        $active = Category::factory()->create(['is_active' => true]);
        $inactive = Category::factory()->inactive()->create();

        $this->assertTrue($policy->viewAny($customer));
        $this->assertTrue($policy->view($customer, $active));
        $this->assertFalse($policy->view($customer, $inactive));
        $this->assertTrue($policy->view($admin, $inactive));

        $this->assertFalse($policy->create($customer));
        $this->assertTrue($policy->create($admin));

        $this->assertFalse($policy->update($customer, $active));
        $this->assertTrue($policy->update($admin, $active));

        $this->assertFalse($policy->delete($customer, $active));
        $this->assertTrue($policy->delete($admin, $active));
    }

    public function test_product_policy_matrix(): void
    {
        $policy = new ProductPolicy;
        $customer = User::factory()->customer()->create();
        $admin = User::factory()->admin()->create();
        $inactive = Product::factory()->create(['active' => false]);
        $active = Product::factory()->create(['active' => true]);

        $this->assertTrue($policy->viewAny($customer));
        $this->assertTrue($policy->view($customer, $active));
        $this->assertFalse($policy->view($customer, $inactive));
        $this->assertTrue($policy->view($admin, $inactive));

        $this->assertTrue($policy->create($admin));
        $this->assertFalse($policy->create($customer));

        $this->assertTrue($policy->update($admin, $active));
        $this->assertTrue($policy->delete($admin, $active));
        $this->assertTrue($policy->restore($admin, $active));
        $this->assertTrue($policy->forceDelete($admin, $active));
    }

    public function test_transaction_policy_matrix(): void
    {
        $policy = new TransactionPolicy;
        $owner = User::factory()->customer()->create();
        $other = User::factory()->customer()->create();
        $admin = User::factory()->admin()->create();

        $tx = Transaction::factory()->create(['user_id' => $owner->id]);

        $this->assertFalse($policy->viewAny($owner));
        $this->assertTrue($policy->viewAny($admin));

        $this->assertTrue($policy->view($owner, $tx));
        $this->assertFalse($policy->view($other, $tx));
        $this->assertTrue($policy->view($admin, $tx));

        $this->assertTrue($policy->create($admin));
        $this->assertFalse($policy->create($owner));
        $this->assertTrue($policy->update($admin, $tx));
        $this->assertTrue($policy->delete($admin, $tx));
        $this->assertTrue($policy->restore($admin, $tx));
        $this->assertTrue($policy->forceDelete($admin, $tx));
    }

    public function test_user_policy_matrix(): void
    {
        $policy = new UserPolicy;
        $self = User::factory()->customer()->create();
        $peer = User::factory()->customer()->create();
        $admin = User::factory()->admin()->create();

        $this->assertFalse($policy->viewAny($self));
        $this->assertTrue($policy->viewAny($admin));

        $this->assertTrue($policy->view($self, $self));
        $this->assertFalse($policy->view($self, $peer));
        $this->assertTrue($policy->view($admin, $peer));

        $this->assertTrue($policy->create($admin));
        $this->assertFalse($policy->create($self));

        $this->assertTrue($policy->update($self, $self));
        $this->assertTrue($policy->update($admin, $peer));

        $this->assertFalse($policy->delete($admin, $admin));
        $this->assertTrue($policy->delete($admin, $peer));
        $this->assertTrue($policy->restore($admin, $peer));
        $this->assertTrue($policy->forceDelete($admin, $peer));
    }

    public function test_boq_template_policy_matrix(): void
    {
        $policy = new BoqTemplatePolicy;
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();
        $template = BoqTemplate::factory()->create();

        $this->assertTrue($policy->viewAny($admin));
        $this->assertFalse($policy->viewAny($customer));
        $this->assertTrue($policy->view($admin, $template));
        $this->assertFalse($policy->view($customer, $template));
        $this->assertTrue($policy->create($admin));
        $this->assertFalse($policy->create($customer));
        $this->assertTrue($policy->update($admin, $template));
        $this->assertTrue($policy->delete($admin, $template));
    }

    public function test_workflow_approval_policy_matrix(): void
    {
        $policy = new WorkflowApprovalPolicy;
        $admin = User::factory()->admin()->create();
        $architect = User::factory()->supervisingArchitect()->create();
        $customer = User::factory()->customer()->create();

        $config = WorkflowConfiguration::query()->create([
            'project_id' => null,
            'name' => 'Global WF',
            'description' => null,
            'type' => WorkflowType::Project,
            'status_transitions' => [],
            'approval_requirements' => [],
            'is_global' => true,
            'is_active' => true,
        ]);

        $project = Project::factory()->create();

        $instance = WorkflowInstance::query()->create([
            'workflow_configuration_id' => $config->id,
            'workflowable_type' => Project::class,
            'workflowable_id' => $project->id,
            'status' => WorkflowInstanceStatus::InProgress->value,
        ]);

        $pending = WorkflowApproval::query()->create([
            'workflow_instance_id' => $instance->id,
            'approval_rule_id' => null,
            'approver_role' => 'supervising_architect',
            'action' => WorkflowApprovalAction::Pending,
            'notes' => null,
            'acted_by' => null,
            'acted_at' => null,
        ]);

        $resolved = WorkflowApproval::query()->create([
            'workflow_instance_id' => $instance->id,
            'approval_rule_id' => null,
            'approver_role' => 'supervising_architect',
            'action' => WorkflowApprovalAction::Approved,
            'notes' => null,
            'acted_by' => $architect->id,
            'acted_at' => now(),
        ]);

        $this->assertTrue($policy->respond($admin, $pending));
        $this->assertTrue($policy->respond($architect, $pending));
        $this->assertFalse($policy->respond($customer, $pending));
        $this->assertFalse($policy->respond($architect, $resolved));
    }

    public function test_invoice_policy_matrix(): void
    {
        $policy = new InvoicePolicy;
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();
        $other = User::factory()->customer()->create();
        $contractor = User::factory()->contractor()->create();
        $supplier = SupplierProfile::factory()->create(['user_id' => $contractor->id]);

        $invoice = Invoice::query()->create([
            'invoice_number' => 'INV-20260101-5001',
            'order_id' => null,
            'customer_id' => $customer->id,
            'supplier_id' => $supplier->id,
            'subtotal' => 10,
            'vat_amount' => 1.5,
            'vat_percentage' => 15,
            'total' => 11.5,
            'status' => InvoiceStatus::Draft,
            'due_date' => now()->addDays(7)->toDateString(),
            'zatca_qr_data' => 'dGVzdA==',
            'notes' => null,
        ]);

        $this->assertTrue($policy->viewAny($admin));
        $this->assertTrue($policy->viewAny($customer));
        $this->assertTrue($policy->view($admin, $invoice));
        $this->assertTrue($policy->view($customer, $invoice));
        $this->assertFalse($policy->view($other, $invoice));
        $this->assertTrue($policy->view($contractor, $invoice));

        $this->assertTrue($policy->create($customer));
        $this->assertFalse($policy->create($contractor));

        $this->assertTrue($policy->send($customer, $invoice));
        $this->assertTrue($policy->downloadPdf($customer, $invoice));
        $this->assertTrue($policy->void($admin, $invoice));
        $this->assertFalse($policy->void($customer, $invoice));
    }

    public function test_estimate_policy_matrix(): void
    {
        $policy = new EstimatePolicy;
        $customer = User::factory()->customer()->create();
        $architect = User::factory()->supervisingArchitect()->create();
        $engineer = User::factory()->fieldEngineer()->create();
        $project = Project::factory()->create([
            'customer_id' => $customer->id,
            'supervising_architect_id' => $architect->id,
        ]);
        $estimate = Estimate::factory()->create([
            'project_id' => $project->id,
            'created_by' => $customer->id,
        ]);

        $this->assertTrue($policy->viewAny($customer, $project));
        $this->assertTrue($policy->view($architect, $estimate));
        $this->assertTrue($policy->compare($customer, $project));
        $this->assertTrue($policy->create($customer, $project));
        $this->assertTrue($policy->update($architect, $estimate));
        $this->assertTrue($policy->calculate($architect, $estimate));
        $this->assertTrue($policy->manageItems($customer, $estimate));
        $this->assertTrue($policy->approve($customer, $estimate));
        $this->assertTrue($policy->approve($architect, $estimate));
        $this->assertFalse($policy->approve($engineer, $estimate));
        $this->assertTrue($policy->export($customer, $estimate));
    }

    public function test_document_policy_matrix(): void
    {
        $policy = new DocumentPolicy;
        $customer = User::factory()->customer()->create();
        $admin = User::factory()->admin()->create();
        $stranger = User::factory()->contractor()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);
        $phase = Phase::factory()->create(['project_id' => $project->id]);

        $onProject = Document::query()->create([
            'documentable_type' => Project::class,
            'documentable_id' => $project->id,
            'category' => DocumentCategory::Photo->value,
            'title' => 'Plan',
            'original_filename' => 'p.jpg',
            'storage_path' => 'docs/p.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 120,
            'version' => 1,
            'uploaded_by' => $customer->id,
        ]);

        $onPhase = Document::query()->create([
            'documentable_type' => Phase::class,
            'documentable_id' => $phase->id,
            'category' => DocumentCategory::Photo->value,
            'title' => 'Note',
            'original_filename' => 'n.jpg',
            'storage_path' => 'docs/n.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 80,
            'version' => 1,
            'uploaded_by' => $customer->id,
        ]);

        $this->assertTrue($policy->view($customer, $onProject));
        $this->assertFalse($policy->view($stranger, $onProject));
        $this->assertTrue($policy->view($admin, $onProject));
        $this->assertFalse($policy->view($customer, $onPhase));

        $this->assertTrue($policy->delete($admin, $onProject));
        $this->assertTrue($policy->delete($customer, $onPhase));
        $this->assertFalse($policy->delete($stranger, $onPhase));
    }

    public function test_media_policy_matrix(): void
    {
        $policy = new MediaPolicy;
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $media = Media::query()->create([
            'mediable_type' => null,
            'mediable_id' => null,
            'filename' => 'f.bin',
            'original_filename' => 'f.bin',
            'mime_type' => 'application/octet-stream',
            'disk' => 'public',
            'path' => 'media/f.bin',
            'size_bytes' => 5,
            'uploaded_by' => $owner->id,
        ]);

        $this->assertTrue($policy->viewAny($other));
        $this->assertTrue($policy->view($owner, $media));
        $this->assertFalse($policy->view($other, $media));
        $this->assertTrue($policy->view($admin, $media));
        $this->assertTrue($policy->create($other));
        $this->assertTrue($policy->delete($owner, $media));
        $this->assertFalse($policy->delete($other, $media));
    }

    public function test_conversation_policy_matrix(): void
    {
        $policy = new ConversationPolicy;
        $a = User::factory()->create();
        $b = User::factory()->create();
        $stranger = User::factory()->create();

        $conversation = Conversation::query()->create([
            'project_id' => null,
            'title' => null,
            'type' => 'direct',
        ]);

        ConversationParticipant::query()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $a->id,
            'last_read_at' => null,
            'joined_at' => now(),
        ]);
        ConversationParticipant::query()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $b->id,
            'last_read_at' => null,
            'joined_at' => now(),
        ]);

        $this->assertTrue($policy->viewAny($stranger));
        $this->assertTrue($policy->view($a, $conversation));
        $this->assertFalse($policy->view($stranger, $conversation));
        $this->assertTrue($policy->create($a));
        $this->assertTrue($policy->send($b, $conversation));
        $this->assertFalse($policy->markRead($stranger, $conversation));
    }

    public function test_payment_policy_matrix(): void
    {
        $policy = new PaymentPolicy;
        $customer = User::factory()->customer()->create();
        $admin = User::factory()->admin()->create();
        $contractor = User::factory()->contractor()->create();

        $payment = Payment::query()->create([
            'payable_type' => User::class,
            'payable_id' => $customer->id,
            'user_id' => $customer->id,
            'amount' => 25.50,
            'currency' => 'SAR',
            'method' => PaymentMethod::Mada,
            'status' => PaymentStatus::Completed,
            'gateway_reference' => 'gw-1',
            'paid_at' => now(),
        ]);

        $this->assertTrue($policy->viewAny($customer));
        $this->assertTrue($policy->viewAny($admin));
        $this->assertFalse($policy->viewAny($contractor));

        $this->assertTrue($policy->view($customer, $payment));
        $this->assertTrue($policy->view($admin, $payment));
        $this->assertFalse($policy->view($contractor, $payment));
    }

    public function test_supplier_profile_policy_matrix(): void
    {
        $policy = new SupplierProfilePolicy;
        $contractor = User::factory()->contractor()->create();
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();
        $profile = SupplierProfile::factory()->create(['user_id' => $contractor->id]);

        $this->assertTrue($policy->create($contractor));
        $this->assertFalse($policy->create($customer));

        $this->assertTrue($policy->update($contractor, $profile));
        $this->assertTrue($policy->update($admin, $profile));
        $this->assertFalse($policy->update($customer, $profile));

        $this->assertTrue($policy->verify($admin, $profile));
        $this->assertFalse($policy->verify($contractor, $profile));
    }

    public function test_rfq_policy_matrix(): void
    {
        $policy = new RfqPolicy;
        $customer = User::factory()->customer()->create();
        $otherCustomer = User::factory()->customer()->create();
        $admin = User::factory()->admin()->create();
        $contractor = User::factory()->contractor()->create();
        $supplier = SupplierProfile::factory()->create(['user_id' => $contractor->id]);

        $rfq = Rfq::factory()->create(['created_by' => $customer->id]);
        RfqTarget::query()->create([
            'rfq_id' => $rfq->id,
            'supplier_id' => $supplier->id,
            'invited_at' => now(),
        ]);

        $this->assertTrue($policy->viewAny($customer));
        $this->assertTrue($policy->viewAny($contractor));
        $this->assertTrue($policy->viewAny($admin));

        $this->assertTrue($policy->view($customer, $rfq));
        $this->assertFalse($policy->view($otherCustomer, $rfq));
        $this->assertTrue($policy->view($contractor, $rfq));
        $this->assertTrue($policy->view($admin, $rfq));

        $this->assertTrue($policy->create($customer));
        $this->assertFalse($policy->create($contractor));

        $this->assertTrue($policy->send($customer, $rfq));
        $this->assertTrue($policy->submitQuotation($contractor, $rfq));
        $this->assertFalse($policy->submitQuotation($customer, $rfq));

        $this->assertTrue($policy->close($customer, $rfq));
        $this->assertTrue($policy->beginEvaluation($customer, $rfq));
    }

    public function test_quotation_policy_matrix(): void
    {
        $policy = new QuotationPolicy;
        $customer = User::factory()->customer()->create();
        $admin = User::factory()->admin()->create();
        $contractor = User::factory()->contractor()->create();
        $supplier = SupplierProfile::factory()->create(['user_id' => $contractor->id]);

        $rfq = Rfq::factory()->create(['created_by' => $customer->id]);
        RfqTarget::query()->create([
            'rfq_id' => $rfq->id,
            'supplier_id' => $supplier->id,
            'invited_at' => now(),
        ]);

        $quotation = Quotation::factory()->create([
            'rfq_id' => $rfq->id,
            'supplier_id' => $supplier->id,
            'status' => QuotationStatus::Submitted,
        ]);

        $this->assertTrue($policy->viewAnyForRfq($customer, $rfq));
        $this->assertTrue($policy->viewAnyForRfq($contractor, $rfq));
        $this->assertTrue($policy->submitForRfq($contractor, $rfq));

        $this->assertTrue($policy->view($customer, $quotation));
        $this->assertTrue($policy->view($contractor, $quotation));
        $this->assertTrue($policy->view($admin, $quotation));

        $this->assertTrue($policy->accept($customer, $rfq, $quotation));

        $this->assertFalse($policy->convertToOrder($customer, $quotation));

        $quotation->update(['status' => QuotationStatus::Accepted]);

        $this->assertTrue($policy->convertToOrder($admin, $quotation));
        $this->assertTrue($policy->convertToOrder($customer, $quotation));
        $this->assertFalse($policy->convertToOrder($contractor, $quotation));
    }
}
