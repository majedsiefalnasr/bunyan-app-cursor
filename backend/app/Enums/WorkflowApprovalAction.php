<?php

namespace App\Enums;

enum WorkflowApprovalAction: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
