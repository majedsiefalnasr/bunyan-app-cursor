<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification;

class PlatformDatabaseNotification extends DatabaseNotification
{
    protected $fillable = [
        'id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'channel',
        'data',
        'read_at',
    ];
}
