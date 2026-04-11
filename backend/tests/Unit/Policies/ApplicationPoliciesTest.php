<?php

namespace Tests\Unit\Policies;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Phase;
use App\Models\Product;
use App\Models\Project;
use App\Models\Report;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\User;
use App\Policies\OrderPolicy;
use App\Policies\PhasePolicy;
use App\Policies\ProductPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\ReportPolicy;
use App\Policies\TaskPolicy;
use App\Policies\TransactionPolicy;
use App\Policies\UserPolicy;
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
        $this->assertFalse($policy->update($contractor, $project));
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
        $admin = User::factory()->admin()->create();
        $field = User::factory()->fieldEngineer()->create();

        $project = Project::factory()->create([
            'customer_id' => $customer->id,
            'contractor_id' => $contractor->id,
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

        $this->assertTrue($policy->create($contractor));
        $this->assertTrue($policy->create($architect));
        $this->assertFalse($policy->create($customer));

        $this->assertTrue($policy->update($contractor, $task));
        $this->assertTrue($policy->update($architect, $task));
        $this->assertTrue($policy->update($assignee, $task));
        $this->assertFalse($policy->update($customer, $task));

        $this->assertTrue($policy->delete($contractor, $task));
        $this->assertFalse($policy->delete($architect, $task));
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

        $this->assertFalse($policy->viewAny($customer));
        $this->assertTrue($policy->viewAny($admin));

        $this->assertTrue($policy->view($customer, $order));
        $this->assertFalse($policy->view($other, $order));
        $this->assertTrue($policy->view($admin, $order));

        $this->assertTrue($policy->create($customer));
        $this->assertTrue($policy->create(User::factory()->contractor()->create()));
        $this->assertFalse($policy->create(User::factory()->fieldEngineer()->create()));

        $this->assertTrue($policy->update($customer, $order));
        $this->assertFalse($policy->update($other, $order));

        $this->assertTrue($policy->delete($customer, $order));

        $processing = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => OrderStatus::Processing,
        ]);
        $this->assertFalse($policy->delete($customer, $processing));

        $this->assertTrue($policy->restore($customer, $order));
        $this->assertTrue($policy->forceDelete($admin, $order));
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
}
