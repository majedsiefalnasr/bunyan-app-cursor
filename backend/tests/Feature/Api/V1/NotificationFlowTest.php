<?php

namespace Tests\Feature\Api\V1;

use App\Enums\NotificationType;
use App\Models\User;
use App\Notifications\GenericDatabaseNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_list_notifications(): void
    {
        $this->getJson('/api/v1/notifications')->assertStatus(401);
    }

    public function test_user_lists_notifications(): void
    {
        $user = User::factory()->customer()->create();
        $user->notify(new GenericDatabaseNotification(
            NotificationType::General,
            'عنوان',
            'Title',
            'نص',
            'Body',
        ));

        $response = $this->actingAs($user)->getJson('/api/v1/notifications');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertGreaterThanOrEqual(1, count($response->json('data')));
    }

    public function test_user_can_mark_notification_read(): void
    {
        $user = User::factory()->customer()->create();
        $user->notify(new GenericDatabaseNotification(
            NotificationType::Orders,
            'طلب',
            'Order',
            'تفاصيل',
            'Details',
        ));

        $id = $user->notifications()->firstOrFail()->id;

        $response = $this->actingAs($user)->putJson("/api/v1/notifications/{$id}/read");

        $response->assertOk();
        $this->assertNotNull($user->notifications()->whereKey($id)->value('read_at'));
    }

    public function test_user_cannot_mark_other_users_notification(): void
    {
        $owner = User::factory()->customer()->create();
        $intruder = User::factory()->customer()->create();
        $owner->notify(new GenericDatabaseNotification(
            NotificationType::General,
            'خاص',
            'Private',
            'لا',
            'No',
        ));

        $id = $owner->notifications()->firstOrFail()->id;

        $this->actingAs($intruder)->putJson("/api/v1/notifications/{$id}/read")
            ->assertStatus(404);
    }

    public function test_unread_count_returns_expected_value(): void
    {
        $user = User::factory()->customer()->create();
        $user->notify(new GenericDatabaseNotification(
            NotificationType::Projects,
            'مشروع',
            'Project',
            'نص',
            'Text',
        ));

        $this->actingAs($user)->getJson('/api/v1/notifications/unread-count')
            ->assertOk()
            ->assertJsonPath('data.count', 1);
    }

    public function test_mark_all_read_clears_unread(): void
    {
        $user = User::factory()->customer()->create();
        $user->notify(new GenericDatabaseNotification(
            NotificationType::Approvals,
            'موافقة',
            'Approval',
            'انتظر',
            'Wait',
        ));

        $this->actingAs($user)->putJson('/api/v1/notifications/read-all')->assertOk();

        $this->actingAs($user)->getJson('/api/v1/notifications/unread-count')
            ->assertJsonPath('data.count', 0);
    }

    public function test_preferences_returns_all_types(): void
    {
        $user = User::factory()->customer()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/notification-preferences');

        $response->assertOk();
        $types = collect($response->json('data'))->pluck('type')->all();
        $this->assertEqualsCanonicalizing(NotificationType::values(), $types);
    }

    public function test_preferences_update_persists(): void
    {
        $user = User::factory()->customer()->create();

        $this->actingAs($user)->putJson('/api/v1/notification-preferences', [
            'preferences' => [
                [
                    'type' => NotificationType::General->value,
                    'email_enabled' => false,
                    'sms_enabled' => true,
                    'push_enabled' => false,
                ],
            ],
        ])->assertOk();

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
            'type' => NotificationType::General->value,
            'email_enabled' => false,
            'sms_enabled' => true,
        ]);
    }

    public function test_preferences_update_rejects_unknown_type(): void
    {
        $user = User::factory()->customer()->create();

        $this->actingAs($user)->putJson('/api/v1/notification-preferences', [
            'preferences' => [
                [
                    'type' => 'unknown_type',
                    'email_enabled' => true,
                ],
            ],
        ])->assertStatus(422);
    }
}
