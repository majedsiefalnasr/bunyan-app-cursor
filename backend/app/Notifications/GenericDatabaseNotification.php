<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class GenericDatabaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public NotificationType $preferenceType,
        public string $titleAr,
        public string $titleEn,
        public string $bodyAr,
        public string $bodyEn,
    ) {
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'preference_type' => $this->preferenceType->value,
            'title_ar' => $this->titleAr,
            'title_en' => $this->titleEn,
            'body_ar' => $this->bodyAr,
            'body_en' => $this->bodyEn,
        ];
    }
}
