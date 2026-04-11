<?php

namespace Tests\Unit\Http;

use App\Http\Resources\Api\V1\OrderResource;
use App\Http\Resources\Api\V1\PhaseResource;
use App\Http\Resources\Api\V1\ProductResource;
use App\Http\Resources\Api\V1\ProjectResource;
use App\Http\Resources\Api\V1\ReportResource;
use App\Http\Resources\Api\V1\TaskResource;
use App\Http\Resources\Api\V1\TransactionResource;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Phase;
use App\Models\Product;
use App\Models\Project;
use App\Models\Report;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Tests\TestCase;

class ApiV1ResourcesTest extends TestCase
{
    use RefreshDatabase;

    private function request(): Request
    {
        return Request::create('/api/v1', 'GET');
    }

    public function test_user_resource_shape(): void
    {
        $user = User::factory()->create();
        $data = (new UserResource($user))->toArray($this->request());

        $this->assertSame($user->id, $data['id']);
        $this->assertArrayHasKey('email', $data);
    }

    public function test_project_resource_with_counts_and_relations(): void
    {
        $project = Project::factory()->create();
        Phase::factory()->count(2)->create(['project_id' => $project->id]);
        $project->load(['customer', 'contractor', 'supervisingArchitect']);
        $project->loadCount(['phases', 'tasks']);

        $data = (new ProjectResource($project))->toArray($this->request());

        $this->assertSame($project->id, $data['id']);
        $this->assertSame(2, $data['phases_count']);
    }

    public function test_phase_resource_shape(): void
    {
        $phase = Phase::factory()->create();
        $phase->load('project');

        $data = (new PhaseResource($phase))->toArray($this->request());
        $this->assertSame($phase->id, $data['id']);
    }

    public function test_task_resource_shape(): void
    {
        $task = Task::factory()->create();
        $task->load(['phase', 'assignee']);

        $data = (new TaskResource($task))->toArray($this->request());
        $this->assertSame($task->id, $data['id']);
    }

    public function test_report_resource_shape(): void
    {
        $report = Report::factory()->create();
        $report->load(['project', 'creator']);

        $data = (new ReportResource($report))->toArray($this->request());
        $this->assertSame($report->id, $data['id']);
    }

    public function test_transaction_resource_shape(): void
    {
        $tx = Transaction::factory()->create();
        $tx->load(['user', 'project', 'order']);

        $data = (new TransactionResource($tx))->toArray($this->request());
        $this->assertSame($tx->id, $data['id']);
    }

    public function test_product_resource_shape(): void
    {
        $product = Product::factory()->create();
        $data = (new ProductResource($product))->toArray($this->request());

        $this->assertSame($product->id, $data['id']);
    }

    public function test_order_resource_with_items(): void
    {
        $order = Order::factory()->create();
        $product = Product::factory()->create();
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 10.50,
            'subtotal' => 21.00,
        ]);
        $order->load('items.product');

        $data = (new OrderResource($order))->resolve();
        $this->assertSame($order->id, $data['id']);
        $this->assertInstanceOf(AnonymousResourceCollection::class, $data['items']);
        $this->assertCount(1, $data['items']->collection);
    }
}
