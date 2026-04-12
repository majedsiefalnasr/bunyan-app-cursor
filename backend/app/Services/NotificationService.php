<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\NotificationRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function __construct(private NotificationRepository $notifications)
    {
    }

    public function list(User $user, int $perPage): LengthAwarePaginator
    {
        return $this->notifications->paginateForUser($user, $perPage);
    }

    public function markRead(User $user, string $id): bool
    {
        $notification = $this->notifications->findForUser($user, $id);
        if ($notification === null) {
            return false;
        }

        $notification->markAsRead();

        Log::info('Notification marked read', [
            'action' => 'notification.read',
            'user_id' => $user->id,
            'notification_id' => $id,
        ]);

        return true;
    }

    public function markAllRead(User $user): int
    {
        $count = $this->notifications->markAllRead($user);

        Log::info('Notifications marked read (bulk)', [
            'action' => 'notification.read_all',
            'user_id' => $user->id,
            'updated' => $count,
        ]);

        return $count;
    }

    public function unreadCount(User $user): int
    {
        return $this->notifications->unreadCount($user);
    }
}
