<?php

namespace App\Services;

use App\Enums\NotificationType;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Repositories\NotificationPreferenceRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class NotificationPreferenceService
{
    public function __construct(private NotificationPreferenceRepository $preferences)
    {
    }

    /**
     * @return Collection<int, NotificationPreference>
     */
    public function mergedForUser(User $user): Collection
    {
        $stored = $this->preferences->forUser($user);

        return collect(NotificationType::cases())->map(function (NotificationType $type) use ($user, $stored) {
            $row = $stored->get($type->value);
            if ($row !== null) {
                return $row;
            }

            return new NotificationPreference([
                'user_id' => $user->id,
                'type' => $type->value,
                'email_enabled' => true,
                'sms_enabled' => false,
                'push_enabled' => false,
            ]);
        });
    }

    /**
     * @param  list<array{type: string, email_enabled?: bool, sms_enabled?: bool, push_enabled?: bool}>  $items
     * @return Collection<int, NotificationPreference>
     */
    public function sync(User $user, array $items): Collection
    {
        $saved = collect();

        foreach ($items as $item) {
            $saved->push($this->preferences->upsertRow(
                $user,
                $item['type'],
                (bool) ($item['email_enabled'] ?? true),
                (bool) ($item['sms_enabled'] ?? false),
                (bool) ($item['push_enabled'] ?? false),
            ));
        }

        Log::info('Notification preferences updated', [
            'action' => 'notification.preferences.updated',
            'user_id' => $user->id,
            'count' => $saved->count(),
        ]);

        return $saved;
    }
}
