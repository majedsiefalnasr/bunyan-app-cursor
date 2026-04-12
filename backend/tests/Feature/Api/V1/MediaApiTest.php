<?php

namespace Tests\Feature\Api\V1;

use App\Models\Media;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_upload_media(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('photo.jpg', 20, 20);

        $response = $this->post('/api/v1/media/upload', [
            'file' => $file,
        ]);

        $response->assertStatus(401);
    }

    public function test_user_can_upload_image_and_list_own_media(): void
    {
        Storage::fake('public');
        $user = User::factory()->customer()->create();
        $file = UploadedFile::fake()->image('photo.jpg', 20, 20);

        $upload = $this->actingAs($user)->post('/api/v1/media/upload', [
            'file' => $file,
            'collection' => 'gallery',
        ]);

        $upload->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.collection', 'gallery');

        $id = (int) $upload->json('data.id');
        $this->assertGreaterThan(0, $id);

        $list = $this->actingAs($user)->getJson('/api/v1/media');
        $list->assertOk()->assertJsonPath('success', true);
        $ids = collect($list->json('data'))->pluck('id')->all();
        $this->assertContains($id, $ids);
    }

    public function test_user_cannot_view_another_users_media(): void
    {
        Storage::fake('public');
        $owner = User::factory()->customer()->create();
        $stranger = User::factory()->contractor()->create();
        $file = UploadedFile::fake()->image('x.jpg', 10, 10);

        $upload = $this->actingAs($owner)->post('/api/v1/media/upload', ['file' => $file]);
        $upload->assertCreated();
        $mediaId = (int) $upload->json('data.id');

        $this->actingAs($stranger)->getJson("/api/v1/media/{$mediaId}")
            ->assertStatus(403);
    }

    public function test_owner_can_delete_media(): void
    {
        Storage::fake('public');
        $user = User::factory()->customer()->create();
        $file = UploadedFile::fake()->image('d.jpg', 10, 10);

        $upload = $this->actingAs($user)->post('/api/v1/media/upload', ['file' => $file]);
        $mediaId = (int) $upload->json('data.id');

        $this->actingAs($user)->deleteJson("/api/v1/media/{$mediaId}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('media', ['id' => $mediaId]);
    }

    public function test_upload_attached_to_project_requires_view_permission(): void
    {
        Storage::fake('public');
        $customer = User::factory()->customer()->create();
        $other = User::factory()->contractor()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $file = UploadedFile::fake()->image('p.jpg', 10, 10);

        $this->actingAs($other)->post('/api/v1/media/upload', [
            'file' => $file,
            'mediable_type' => Project::class,
            'mediable_id' => $project->id,
        ])->assertStatus(403);

        $ok = $this->actingAs($customer)->post('/api/v1/media/upload', [
            'file' => $file,
            'mediable_type' => Project::class,
            'mediable_id' => $project->id,
        ]);

        $ok->assertCreated()
            ->assertJsonPath('data.mediable_id', $project->id);
    }

    public function test_prune_temporary_command_removes_old_rows(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $media = Media::query()->create([
            'mediable_type' => null,
            'mediable_id' => null,
            'collection' => 'default',
            'filename' => 'f.jpg',
            'original_filename' => 'f.jpg',
            'mime_type' => 'image/jpeg',
            'disk' => 'public',
            'path' => 'media/test/f.jpg',
            'thumb_path' => null,
            'size_bytes' => 100,
            'dimensions_json' => null,
            'alt_text_ar' => null,
            'alt_text_en' => null,
            'sort_order' => 0,
            'uploaded_by' => $user->id,
            'is_temporary' => true,
        ]);

        Media::query()->whereKey($media->id)->update([
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

        $this->artisan('media:prune-temporary', ['--hours' => 24])->assertSuccessful();

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    public function test_non_admin_cannot_filter_media_by_user_id(): void
    {
        $user = User::factory()->customer()->create();

        $this->actingAs($user)->getJson('/api/v1/media?user_id=1')
            ->assertStatus(422);
    }

    public function test_admin_can_filter_media_by_user_id(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $target = User::factory()->customer()->create();
        $file = UploadedFile::fake()->image('a.jpg', 10, 10);

        $this->actingAs($target)->post('/api/v1/media/upload', ['file' => $file])->assertCreated();

        $list = $this->actingAs($admin)->getJson('/api/v1/media?user_id='.$target->id);
        $list->assertOk();
        $this->assertNotEmpty($list->json('data'));
    }
}
