<?php

namespace App\Models\Concerns;

use App\Enums\ActivityLogAction;
use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Model;

trait LogsModelActivity
{
    public static function bootLogsModelActivity(): void
    {
        static::created(function (Model $model): void {
            if (! $model instanceof static) {
                return;
            }

            app(ActivityLogService::class)->record($model, ActivityLogAction::Created);
        });

        static::updated(function (Model $model): void {
            if (! $model instanceof static) {
                return;
            }

            $changes = $model->getChanges();
            unset($changes['updated_at']);

            if ($changes === []) {
                return;
            }

            $keys = array_keys($changes);
            $old = collect($model->getOriginal())->only($keys)->all();
            $new = collect($changes)->only($keys)->all();

            app(ActivityLogService::class)->record($model, ActivityLogAction::Updated, [
                'old' => $old,
                'new' => $new,
            ]);
        });

        static::deleted(function (Model $model): void {
            if (! $model instanceof static) {
                return;
            }

            app(ActivityLogService::class)->record($model, ActivityLogAction::Deleted);
        });
    }
}
