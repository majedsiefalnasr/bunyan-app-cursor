<?php

namespace Tests\Unit\Http;

use App\Enums\ActivityLogAction;
use App\Enums\DocumentCategory;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentAttemptStatus;
use App\Enums\PaymentAttemptType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ProjectRole;
use App\Enums\QuotationStatus;
use App\Enums\RfqStatus;
use App\Enums\WorkflowApprovalAction;
use App\Enums\WorkflowInstanceStatus;
use App\Enums\WorkflowType;
use App\Http\Resources\Api\V1\ActivityLogResource;
use App\Http\Resources\Api\V1\BoqTemplateResource;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Http\Resources\Api\V1\ConversationResource;
use App\Http\Resources\Api\V1\DocumentResource;
use App\Http\Resources\Api\V1\DocumentVersionResource;
use App\Http\Resources\Api\V1\EstimateItemResource;
use App\Http\Resources\Api\V1\EstimateResource;
use App\Http\Resources\Api\V1\InventoryResource;
use App\Http\Resources\Api\V1\InvoiceItemResource;
use App\Http\Resources\Api\V1\InvoiceResource;
use App\Http\Resources\Api\V1\MediaResource;
use App\Http\Resources\Api\V1\MessageResource;
use App\Http\Resources\Api\V1\NotificationPreferenceResource;
use App\Http\Resources\Api\V1\NotificationResource;
use App\Http\Resources\Api\V1\PaymentAttemptResource;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Http\Resources\Api\V1\PermissionResource;
use App\Http\Resources\Api\V1\PriceTierResource;
use App\Http\Resources\Api\V1\ProductMediaResource;
use App\Http\Resources\Api\V1\ProductVariantResource;
use App\Http\Resources\Api\V1\ProjectInvitationResource;
use App\Http\Resources\Api\V1\ProjectMemberResource;
use App\Http\Resources\Api\V1\QuotationItemResource;
use App\Http\Resources\Api\V1\QuotationResource;
use App\Http\Resources\Api\V1\RfqItemResource;
use App\Http\Resources\Api\V1\RfqResource;
use App\Http\Resources\Api\V1\RoleResource;
use App\Http\Resources\Api\V1\StockMovementResource;
use App\Http\Resources\Api\V1\SupplierProfileResource;
use App\Http\Resources\Api\V1\TaskCommentResource;
use App\Http\Resources\Api\V1\UserAdminResource;
use App\Http\Resources\Api\V1\WorkflowApprovalResource;
use App\Http\Resources\Api\V1\WorkflowDefinitionResource;
use App\Http\Resources\Api\V1\WorkflowInstanceResource;
use App\Models\ActivityLog;
use App\Models\ApprovalRule;
use App\Models\BoqTemplate;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\Inventory;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Media;
use App\Models\Message;
use App\Models\NotificationPreference;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Models\Permission;
use App\Models\PlatformDatabaseNotification;
use App\Models\PriceTier;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Models\ProductVariant;
use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\ProjectMember;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Rfq;
use App\Models\RfqItem;
use App\Models\Role;
use App\Models\StockMovement;
use App\Models\SupplierProfile;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use App\Models\WorkflowApproval;
use App\Models\WorkflowConfiguration;
use App\Models\WorkflowInstance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Executes JsonResource::toArray() for API v1
 * resources so presentation code stays inside the enforced coverage gate.
 */
class ApiV1JsonResourcesCoverageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('public');
    }

    private function request(): Request
    {
        return Request::create('/api/v1', 'GET');
    }

    public function test_platform_catalog_and_identity_resources(): void
    {
        $req = $this->request();

        $role = Role::query()->where('name', 'customer')->firstOrFail();
        $role->loadCount(['users', 'permissions']);
        $this->assertIsArray((new RoleResource($role))->toArray($req));

        $permission = Permission::query()->firstOrFail();
        $this->assertIsArray((new PermissionResource($permission))->toArray($req));

        $category = Category::factory()->create();
        $this->assertIsArray((new CategoryResource($category))->toArray($req));

        $boq = BoqTemplate::factory()->create();
        $this->assertIsArray((new BoqTemplateResource($boq))->toArray($req));

        $admin = User::factory()->admin()->create();
        $this->assertIsArray((new UserAdminResource($admin))->toArray($req));

        $pref = NotificationPreference::factory()->for($admin)->create();
        $this->assertIsArray((new NotificationPreferenceResource($pref))->toArray($req));

        $notif = PlatformDatabaseNotification::query()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\GenericNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $admin->id,
            'channel' => 'database',
            'data' => json_encode([
                'preference_type' => 'general',
                'title_ar' => 'عنوان',
                'title_en' => 'Title',
                'body_ar' => 'نص',
                'body_en' => 'Body',
            ], JSON_THROW_ON_ERROR),
            'read_at' => null,
        ]);
        $this->assertIsArray((new NotificationResource($notif))->toArray($req));
    }

    public function test_inventory_movements_media_and_product_children(): void
    {
        $req = $this->request();
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $inventory = Inventory::query()->create([
            'product_id' => $product->id,
            'variant_id' => null,
            'warehouse_location' => 'default',
            'quantity' => 4,
            'reserved_quantity' => 1,
            'min_quantity' => 0,
        ]);
        $inventory->load('product');
        $this->assertIsArray((new InventoryResource($inventory))->toArray($req));

        $variant = ProductVariant::query()->create([
            'product_id' => $product->id,
            'name' => 'Large',
            'sku' => 'SKU-'.$product->id.'-'.Str::random(6),
            'price_modifier' => 10,
            'stock_quantity' => 0,
            'attributes_json' => null,
            'is_active' => true,
        ]);

        $inventoryWithVariant = Inventory::query()->create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'warehouse_location' => 'east',
            'quantity' => 2,
            'reserved_quantity' => 0,
            'min_quantity' => 0,
        ]);
        $inventoryWithVariant->load(['product', 'variant']);
        $this->assertIsArray((new InventoryResource($inventoryWithVariant))->toArray($req));

        $this->assertIsArray((new ProductVariantResource($variant))->toArray($req));

        $pm = ProductMedia::query()->create([
            'product_id' => $product->id,
            'type' => 'image',
            'path' => 'p/x.jpg',
            'sort_order' => 0,
        ]);
        $this->assertIsArray((new ProductMediaResource($pm))->toArray($req));

        $movement = StockMovement::query()->create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'type' => 'adjustment',
            'quantity' => 1,
            'reference_type' => null,
            'reference_id' => null,
            'notes' => 'test',
            'created_by' => $user->id,
            'created_at' => now(),
        ]);
        $this->assertIsArray((new StockMovementResource($movement))->toArray($req));

        $tier = PriceTier::query()->create([
            'product_id' => $product->id,
            'product_variant_id' => null,
            'min_quantity' => 1,
            'max_quantity' => 10,
            'unit_price' => 99.99,
        ]);
        $this->assertIsArray((new PriceTierResource($tier))->toArray($req));

        $media = Media::query()->create([
            'mediable_type' => null,
            'mediable_id' => null,
            'filename' => 'a.bin',
            'original_filename' => 'a.bin',
            'mime_type' => 'application/octet-stream',
            'disk' => 'public',
            'path' => 'media/a.bin',
            'size_bytes' => 4,
            'uploaded_by' => $user->id,
        ]);
        $this->assertIsArray((new MediaResource($media))->toArray($req));
    }

    public function test_project_task_document_and_conversation_resources(): void
    {
        $req = $this->request();
        $owner = User::factory()->customer()->create();
        $peer = User::factory()->create();
        $project = Project::factory()->create(['customer_id' => $owner->id]);

        $document = Document::query()->create([
            'documentable_type' => Project::class,
            'documentable_id' => $project->id,
            'category' => DocumentCategory::Photo->value,
            'title' => 'Doc',
            'original_filename' => 'd.jpg',
            'storage_path' => 'docs/d.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 500,
            'version' => 1,
            'uploaded_by' => $owner->id,
        ]);
        $this->assertIsArray((new DocumentResource($document))->toArray($req));

        $version = DocumentVersion::query()->create([
            'document_id' => $document->id,
            'version' => 1,
            'storage_path' => 'docs/v1.jpg',
            'size_bytes' => 500,
            'uploaded_by' => $owner->id,
        ]);
        $this->assertIsArray((new DocumentVersionResource($version))->toArray($req));

        $task = Task::factory()->create(['project_id' => $project->id]);
        $comment = TaskComment::query()->create([
            'task_id' => $task->id,
            'user_id' => $owner->id,
            'body' => 'note',
        ]);
        $comment->load('user');
        $this->assertIsArray((new TaskCommentResource($comment))->toArray($req));

        $member = ProjectMember::query()->create([
            'project_id' => $project->id,
            'user_id' => $peer->id,
            'project_role' => ProjectRole::Engineer->value,
            'joined_at' => now(),
        ]);
        $member->load('user');
        $this->assertIsArray((new ProjectMemberResource($member))->toArray($req));

        $invitation = ProjectInvitation::query()->create([
            'project_id' => $project->id,
            'email' => 'guest@example.test',
            'project_role' => ProjectRole::Viewer->value,
            'token_hash' => hash('sha256', 'token-'.$project->id),
            'invited_by' => $owner->id,
            'accepted_at' => null,
            'expires_at' => now()->addDays(7),
        ]);
        $invitation->load('invitedByUser');
        $this->assertIsArray((new ProjectInvitationResource($invitation))->toArray($req));

        $conversation = Conversation::query()->create([
            'project_id' => null,
            'title' => null,
            'type' => 'direct',
        ]);
        ConversationParticipant::query()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $owner->id,
            'last_read_at' => null,
            'joined_at' => now(),
        ]);
        ConversationParticipant::query()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $peer->id,
            'last_read_at' => null,
            'joined_at' => now(),
        ]);

        $message = Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $owner->id,
            'body' => 'hello',
            'type' => 'text',
            'attachment_path' => null,
        ]);
        $conversation->load(['participants.user', 'latestMessage.sender']);
        $this->assertIsArray((new ConversationResource($conversation))->toArray($req));
        $message->load('sender');
        $this->assertIsArray((new MessageResource($message))->toArray($req));

        $log = ActivityLog::query()->create([
            'user_id' => $owner->id,
            'action' => ActivityLogAction::Created,
            'subject_type' => $project->getMorphClass(),
            'subject_id' => $project->id,
            'properties_json' => ['k' => 'v'],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'created_at' => now(),
        ]);
        $log->load('actor');
        $this->assertIsArray((new ActivityLogResource($log))->toArray($req));
    }

    public function test_commerce_estimates_and_payment_resources(): void
    {
        $req = $this->request();
        $user = User::factory()->customer()->create();
        $project = Project::factory()->create(['customer_id' => $user->id]);

        $estimate = Estimate::factory()->create(['project_id' => $project->id, 'created_by' => $user->id]);
        $item = EstimateItem::factory()->create(['estimate_id' => $estimate->id]);
        $estimate->load('items');
        $this->assertIsArray((new EstimateResource($estimate))->toArray($req));
        $this->assertIsArray((new EstimateItemResource($item))->toArray($req));

        $rfq = Rfq::factory()->create([
            'project_id' => $project->id,
            'created_by' => $user->id,
            'status' => RfqStatus::Quoting->value,
        ]);
        $rfqItem = RfqItem::factory()->create(['rfq_id' => $rfq->id]);
        $rfq->load('items');
        $this->assertIsArray((new RfqResource($rfq))->toArray($req));
        $this->assertIsArray((new RfqItemResource($rfqItem))->toArray($req));

        $supplier = SupplierProfile::factory()->create();
        $quotation = Quotation::factory()->create([
            'rfq_id' => $rfq->id,
            'supplier_id' => $supplier->id,
            'status' => QuotationStatus::Submitted,
        ]);
        $qItem = QuotationItem::query()->create([
            'quotation_id' => $quotation->id,
            'rfq_item_id' => $rfqItem->id,
            'unit_price' => 10.00,
            'total_price' => 100.00,
            'notes' => null,
        ]);
        $quotation->load(['supplierProfile.user', 'items']);
        $this->assertIsArray((new QuotationResource($quotation))->toArray($req));
        $this->assertIsArray((new QuotationItemResource($qItem))->toArray($req));

        $supplier->load('user');
        $this->assertIsArray((new SupplierProfileResource($supplier))->toArray($req));

        $payment = Payment::query()->create([
            'payable_type' => Project::class,
            'payable_id' => $project->id,
            'user_id' => $user->id,
            'amount' => 50.00,
            'currency' => 'SAR',
            'method' => PaymentMethod::Mada,
            'status' => PaymentStatus::Completed,
            'gateway_reference' => 'g1',
            'paid_at' => now(),
        ]);
        $attempt = PaymentAttempt::query()->create([
            'payment_id' => $payment->id,
            'type' => PaymentAttemptType::Charge,
            'amount' => 50.00,
            'status' => PaymentAttemptStatus::Succeeded,
            'gateway_id' => 'sandbox',
            'gateway_response' => null,
        ]);
        $payment->load('attempts');
        $this->assertIsArray((new PaymentResource($payment))->toArray($req));
        $this->assertIsArray((new PaymentAttemptResource($attempt))->toArray($req));

        $invoice = Invoice::query()->create([
            'invoice_number' => 'INV-20260101-8001',
            'order_id' => null,
            'customer_id' => $user->id,
            'supplier_id' => null,
            'subtotal' => 10,
            'vat_amount' => 1.5,
            'vat_percentage' => 15,
            'total' => 11.5,
            'status' => InvoiceStatus::Draft,
            'due_date' => now()->addDays(7)->toDateString(),
            'zatca_qr_data' => 'dGVzdA==',
            'notes' => null,
        ]);

        $line = InvoiceItem::query()->create([
            'invoice_id' => $invoice->id,
            'description_ar' => 'بند',
            'description_en' => 'Line',
            'quantity' => 1,
            'unit_price' => 10,
            'vat_rate' => 15,
            'line_subtotal' => 10,
            'line_vat' => 1.5,
            'line_total' => 11.5,
        ]);
        $invoice->load('items');
        $this->assertIsArray((new InvoiceResource($invoice))->toArray($req));
        $this->assertIsArray((new InvoiceItemResource($line))->toArray($req));
    }

    public function test_workflow_definition_instance_and_approval_resources(): void
    {
        $req = $this->request();

        $config = WorkflowConfiguration::query()->create([
            'project_id' => null,
            'name' => 'Global',
            'name_ar' => 'عام',
            'name_en' => 'Global',
            'description' => null,
            'type' => WorkflowType::Project,
            'status_transitions' => [],
            'approval_requirements' => [],
            'is_global' => true,
            'is_active' => true,
        ]);

        ApprovalRule::query()->create([
            'workflow_configuration_id' => $config->id,
            'entity_type' => 'project',
            'status_from' => 'draft',
            'status_to' => 'planning',
            'approver_role' => 'supervising_architect',
            'approval_count' => 1,
        ]);

        $config->load('approvalRules');
        $this->assertIsArray((new WorkflowDefinitionResource($config))->toArray($req));

        $project = Project::factory()->create();
        $instance = WorkflowInstance::query()->create([
            'workflow_configuration_id' => $config->id,
            'workflowable_type' => Project::class,
            'workflowable_id' => $project->id,
            'status' => WorkflowInstanceStatus::InProgress->value,
        ]);

        $approval = WorkflowApproval::query()->create([
            'workflow_instance_id' => $instance->id,
            'approval_rule_id' => null,
            'approver_role' => 'supervising_architect',
            'action' => WorkflowApprovalAction::Pending,
            'notes' => null,
            'acted_by' => null,
            'acted_at' => null,
        ]);

        $instance->load(['workflowConfiguration', 'approvals']);
        $this->assertIsArray((new WorkflowInstanceResource($instance))->toArray($req));

        $approval->load('workflowInstance');
        $this->assertIsArray((new WorkflowApprovalResource($approval))->toArray($req));
    }
}
