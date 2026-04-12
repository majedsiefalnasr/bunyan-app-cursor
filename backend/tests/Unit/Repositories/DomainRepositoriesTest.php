<?php

namespace Tests\Unit\Repositories;

use App\Models\ApprovalRule;
use App\Models\Order;
use App\Models\Phase;
use App\Models\Product;
use App\Models\Project;
use App\Models\Report;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WorkflowConfiguration;
use App\Repositories\ApprovalRuleRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PhaseRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\ReportRepository;
use App\Repositories\TaskRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\WorkflowConfigurationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainRepositoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_repository_queries(): void
    {
        $repo = new ProductRepository;
        $sku = Product::factory()->create(['sku' => 'COVERAGE-SKU-1', 'active' => true]);

        $this->assertSame($sku->id, $repo->findBySku('COVERAGE-SKU-1')?->id);

        Product::factory()->count(2)->create(['active' => true, 'quantity_in_stock' => 10]);
        $this->assertGreaterThanOrEqual(1, $repo->allActive(['per_page' => 5])->total());
        $this->assertNotEmpty($repo->allByCategory('building_materials'));
        $this->assertNotEmpty($repo->allInStock());
    }

    public function test_project_repository_queries(): void
    {
        $repo = new ProjectRepository;
        $user = User::factory()->customer()->create();
        $project = Project::factory()->create(['customer_id' => $user->id]);
        Project::factory()->inProgress()->create(['customer_id' => $user->id]);

        $found = $repo->findById($project->id);
        $this->assertNotNull($found);

        $this->assertGreaterThanOrEqual(1, $repo->listForUser($user, ['per_page' => 5])->total());
        $this->assertNotEmpty($repo->allActive());
        $this->assertGreaterThanOrEqual(1, $repo->allByCustomer($user->id, ['per_page' => 5])->total());

        $contractor = User::factory()->contractor()->create();
        Project::factory()->create(['contractor_id' => $contractor->id, 'customer_id' => $user->id]);
        $this->assertGreaterThanOrEqual(1, $repo->allByContractor($contractor->id, ['per_page' => 5])->total());
    }

    public function test_phase_repository_queries(): void
    {
        $repo = new PhaseRepository;
        $project = Project::factory()->create();
        $phase = Phase::factory()->inProgress()->create(['project_id' => $project->id]);

        $this->assertNotNull($repo->findById($phase->id));
        $this->assertGreaterThanOrEqual(1, $repo->allByProject($project->id, ['per_page' => 5])->total());
        $this->assertNotEmpty($repo->allActiveByProject($project->id));
    }

    public function test_task_repository_queries(): void
    {
        $repo = new TaskRepository;
        $phase = Phase::factory()->create();
        $task = Task::factory()->create(['phase_id' => $phase->id]);
        $user = User::factory()->create();
        Task::factory()->create(['phase_id' => $phase->id, 'assigned_to' => $user->id]);
        Task::factory()->inProgress()->create(['phase_id' => $phase->id]);

        $this->assertNotNull($repo->findById($task->id));
        $this->assertGreaterThanOrEqual(1, $repo->allByPhase($phase->id, ['per_page' => 5])->total());
        $this->assertNotEmpty($repo->allAssignedTo($user->id));
        $this->assertNotEmpty($repo->allActiveByPhase($phase->id));
    }

    public function test_report_repository_queries(): void
    {
        $repo = new ReportRepository;
        $project = Project::factory()->create();
        $phase = Phase::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->create(['phase_id' => $phase->id]);
        $report = Report::factory()->create([
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'task_id' => $task->id,
        ]);

        $this->assertNotNull($repo->findById($report->id));
        $this->assertGreaterThanOrEqual(1, $repo->allByProject($project->id, ['per_page' => 5])->total());
        $this->assertNotEmpty($repo->allByPhase($phase->id));
        $this->assertNotEmpty($repo->allByTask($task->id));
    }

    public function test_order_repository_queries(): void
    {
        $repo = new OrderRepository;
        $customer = User::factory()->customer()->create();
        $project = Project::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id, 'project_id' => $project->id]);

        $this->assertNotNull($repo->findById($order->id));
        $this->assertGreaterThanOrEqual(1, $repo->allByCustomer($customer->id, ['per_page' => 5])->total());
        $this->assertNotEmpty($repo->allByProject($project->id));
        Order::factory()->create(['customer_id' => $customer->id, 'status' => 'pending']);
        $this->assertNotEmpty($repo->allPending());
    }

    public function test_transaction_repository_queries(): void
    {
        $repo = new TransactionRepository;
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $tx = Transaction::factory()->create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'status' => 'completed',
        ]);

        $this->assertNotNull($repo->findById($tx->id));
        $this->assertGreaterThanOrEqual(1, $repo->allByUser($user->id, ['per_page' => 5])->total());
        $this->assertNotEmpty($repo->allByProject($project->id));
        $this->assertNotEmpty($repo->allCompleted());
    }

    public function test_workflow_and_approval_repositories(): void
    {
        $project = Project::factory()->create();
        $global = WorkflowConfiguration::query()->create([
            'project_id' => null,
            'name' => 'Global workflow',
            'description' => null,
            'status_transitions' => [],
            'approval_requirements' => [],
            'is_global' => true,
        ]);
        $scoped = WorkflowConfiguration::query()->create([
            'project_id' => $project->id,
            'name' => 'Project workflow',
            'description' => null,
            'status_transitions' => [],
            'approval_requirements' => [],
            'is_global' => false,
        ]);

        $rule = ApprovalRule::query()->create([
            'workflow_configuration_id' => $scoped->id,
            'entity_type' => 'phase',
            'status_from' => 'pending',
            'status_to' => 'in_progress',
            'approver_role' => 'supervising_architect',
            'approval_count' => 1,
        ]);

        $wfRepo = new WorkflowConfigurationRepository;
        $this->assertNotNull($wfRepo->findById($global->id));
        $this->assertNotNull($wfRepo->findGlobal());
        $this->assertNotNull($wfRepo->findByProject($project->id));
        $this->assertTrue($wfRepo->allGlobal()->isNotEmpty());
        $this->assertTrue($wfRepo->allByProject($project->id)->isNotEmpty());

        $arRepo = new ApprovalRuleRepository;
        $this->assertNotNull($arRepo->findById($rule->id));
        $this->assertTrue($arRepo->allByWorkflowConfiguration($scoped->id)->isNotEmpty());
        $this->assertSame(
            $rule->id,
            $arRepo->findRuleFor($scoped->id, 'phase', 'pending', 'in_progress')?->id
        );
    }
}
