<?php

namespace App\Repositories;

use App\Models\PlatformDatabaseNotification;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NotificationRepository
{
    public function paginateForUser(User $user, int $perPage): LengthAwarePaginator
    {
        return $user->notifications()
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function findForUser(User $user, string $id): ?PlatformDatabaseNotification
    {
        /** @var PlatformDatabaseNotification|null $notification */
        $notification = $user->notifications()->whereKey($id)->first();

        return $notification;
    }

    public function unreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    public function markAllRead(User $user): int
    {
        return $user->unreadNotifications()->update(['read_at' => now()]);
    }
}
