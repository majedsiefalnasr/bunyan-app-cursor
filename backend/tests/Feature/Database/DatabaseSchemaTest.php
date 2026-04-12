<?php

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_table_exists_with_required_columns(): void
    {
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasColumns('users', [
            'id', 'name', 'email', 'password', 'role', 'phone', 'active',
            'email_verified_at', 'remember_token', 'created_at', 'updated_at', 'deleted_at',
        ]));
    }

    public function test_roles_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('roles'));
        $this->assertTrue(Schema::hasColumns('roles', ['id', 'name', 'description']));
    }

    public function test_permissions_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('permissions'));
        $this->assertTrue(Schema::hasColumns('permissions', ['id', 'name', 'description']));
    }

    public function test_role_permissions_pivot_exists(): void
    {
        $this->assertTrue(Schema::hasTable('role_permissions'));
        $this->assertTrue(Schema::hasColumns('role_permissions', ['role_id', 'permission_id']));
    }

    public function test_role_user_pivot_exists(): void
    {
        $this->assertTrue(Schema::hasTable('role_user'));
        $this->assertTrue(Schema::hasColumns('role_user', [
            'id', 'user_id', 'role_id', 'assigned_by', 'assigned_at',
        ]));
    }

    public function test_projects_table_exists_with_required_columns(): void
    {
        $this->assertTrue(Schema::hasTable('projects'));
        $this->assertTrue(Schema::hasColumns('projects', [
            'id', 'name', 'name_ar', 'name_en', 'description', 'customer_id', 'contractor_id',
            'supervising_architect_id', 'status', 'budget', 'budget_estimated', 'budget_actual',
            'location', 'city', 'district', 'location_lat', 'location_lng', 'project_type',
            'start_date', 'end_date', 'created_at', 'updated_at', 'deleted_at',
        ]));
    }

    public function test_phases_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('phases'));
        $this->assertTrue(Schema::hasColumns('phases', [
            'id', 'project_id', 'sort_order', 'name', 'name_ar', 'name_en', 'status', 'budget', 'progress', 'deleted_at',
        ]));
    }

    public function test_tasks_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('tasks'));
        $this->assertTrue(Schema::hasColumns('tasks', [
            'id', 'phase_id', 'name', 'status', 'assigned_to', 'deleted_at',
        ]));
    }

    public function test_reports_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('reports'));
    }

    public function test_orders_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('orders'));
        $this->assertTrue(Schema::hasColumns('orders', [
            'id', 'customer_id', 'status', 'total_amount', 'deleted_at',
        ]));
    }

    public function test_order_items_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('order_items'));
        $this->assertTrue(Schema::hasColumns('order_items', ['order_id', 'product_id', 'quantity']));
    }

    public function test_products_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('products'));
    }

    public function test_categories_table_exists_with_required_columns(): void
    {
        $this->assertTrue(Schema::hasTable('categories'));
        $this->assertTrue(Schema::hasColumns('categories', [
            'id', 'parent_id', 'name_ar', 'name_en', 'slug', 'icon', 'sort_order', 'is_active',
            'created_at', 'updated_at', 'deleted_at',
        ]));
    }

    public function test_transactions_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('transactions'));
        $this->assertTrue(Schema::hasColumns('transactions', [
            'id', 'user_id', 'type', 'amount', 'status',
        ]));
    }

    public function test_workflow_configurations_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('workflow_configurations'));
        $this->assertTrue(Schema::hasColumns('workflow_configurations', [
            'id', 'project_id', 'name', 'name_ar', 'name_en', 'description', 'type',
            'status_transitions', 'approval_requirements', 'is_global', 'is_active',
            'created_at', 'updated_at',
        ]));
    }

    public function test_approval_rules_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('approval_rules'));
    }

    public function test_workflow_instances_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('workflow_instances'));
        $this->assertTrue(Schema::hasColumns('workflow_instances', [
            'id', 'workflow_configuration_id', 'workflowable_type', 'workflowable_id', 'status',
            'created_at', 'updated_at',
        ]));
    }

    public function test_workflow_approvals_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('workflow_approvals'));
        $this->assertTrue(Schema::hasColumns('workflow_approvals', [
            'id', 'workflow_instance_id', 'approval_rule_id', 'approver_role', 'action',
            'notes', 'acted_by', 'acted_at', 'created_at', 'updated_at',
        ]));
    }

    public function test_activity_logs_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('activity_logs'));
        $this->assertTrue(Schema::hasColumns('activity_logs', [
            'id', 'user_id', 'action', 'subject_type', 'subject_id', 'properties_json',
            'ip_address', 'user_agent', 'created_at',
        ]));
    }

    public function test_project_members_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('project_members'));
        $this->assertTrue(Schema::hasColumns('project_members', [
            'id', 'project_id', 'user_id', 'project_role', 'joined_at', 'created_at', 'updated_at',
        ]));
    }

    public function test_project_invitations_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('project_invitations'));
        $this->assertTrue(Schema::hasColumns('project_invitations', [
            'id', 'project_id', 'email', 'project_role', 'token_hash', 'invited_by',
            'accepted_at', 'expires_at', 'created_at', 'updated_at',
        ]));
    }
}
