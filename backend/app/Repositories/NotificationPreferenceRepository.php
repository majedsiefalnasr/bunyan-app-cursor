<?php

namespace App\Repositories;

use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationPreferenceRepository
{
    /**
     * @return Collection<string, NotificationPreference>
     */
    public function forUser(User $user): Collection
    {
        return NotificationPreference::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('type');
    }

    public function upsertRow(User $user, string $type, bool $email, bool $sms, bool $push): NotificationPreference
    {
        /** @var NotificationPreference $pref */
        $pref = NotificationPreference::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'type' => $type,
            ],
            [
                'email_enabled' => $email,
                'sms_enabled' => $sms,
                'push_enabled' => $push,
            ],
        );

        return $pref;
    }
}
