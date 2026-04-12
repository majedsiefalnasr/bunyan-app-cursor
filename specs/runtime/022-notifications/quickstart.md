# Notifications — Quickstart

## Backend

```bash
cd backend
composer install
cp .env.example .env # if needed
php artisan migrate
php artisan test --filter=Notification
```

Seed a notification locally (tinker):

```php
$user = \App\Models\User::first();
$user->notify(new \App\Notifications\GenericDatabaseNotification(
    \App\Enums\NotificationType::General,
    'مرحبا',
    'Hello',
    'جرب الإشعارات',
    'Try notifications',
));
```

## Frontend

```bash
cd frontend
npm install
npm run dev
```

Visit `/notifications` and `/notifications/settings` while logged in.
