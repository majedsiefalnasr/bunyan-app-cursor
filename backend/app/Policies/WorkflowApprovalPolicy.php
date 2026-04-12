<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Enums\WorkflowApprovalAction;
use App\Models\User;
use App\Models\WorkflowApproval;

class WorkflowApprovalPolicy
{
    public function respond(User $user, WorkflowApproval $approval): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        if ($approval->action !== WorkflowApprovalAction::Pending) {
            return false;
        }

        return $user->role->value === $approval->approver_role;
    }
}
