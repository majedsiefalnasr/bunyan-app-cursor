# Notifications — Research Notes

## Laravel Notifications (database channel)

- `Illuminate\Notifications\Notifiable` provides `notifications()`, `unreadNotifications()`, `readNotifications()` morph relationships.
- `Illuminate\Notifications\DatabaseNotification` expects `notifications` table columns: `id` (UUID), `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, timestamps.
- Custom columns (e.g. `channel`) require extending the database notification model and overriding the User relationship to the subclass.

## Queues

- `Illuminate\Contracts\Queue\ShouldQueue` on notification classes defers heavy mail/SMS work; database channel still writes synchronously unless using `ShouldQueue` with queued listeners — for simplicity we queue the entire notification class while keeping payload small.

## Nuxt UI

- `UPopover` + `UButton` for bell/dropdown; `USwitch` or checkbox group for preference toggles; `UTable` for history listing.

## Bunyan conventions

- Reuse `BaseController::sendSuccess` / `sendError` patterns.
- Arabic-first `message` strings mirroring existing controllers.
