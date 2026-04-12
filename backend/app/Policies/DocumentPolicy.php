<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Document;
use App\Models\Project;
use App\Models\User;

class DocumentPolicy
{
    public function view(User $user, Document $document): bool
    {
        $parent = $document->documentable;
        if (! $parent instanceof Project) {
            return false;
        }

        return $user->can('view', $parent);
    }

    public function delete(User $user, Document $document): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        if ($document->uploaded_by === $user->id) {
            return true;
        }

        $parent = $document->documentable;
        if ($parent instanceof Project && $parent->customer_id === $user->id) {
            return true;
        }

        return false;
    }
}
