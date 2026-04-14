<?php

namespace Tests\Unit\Services;

use App\Models\Media;
use App\Models\Project;
use App\Models\User;
use App\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MediaServiceTest extends TestCase
{
    use RefreshDatabase;

    private MediaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(MediaService::class);
        Storage::fake('public');
    }

    public function test_store_rejects_invalid_or_disallowed_mime(): void
    {
        $uploader = User::factory()->admin()->create();

        $bad = UploadedFile::fake()->create('x.exe', 10, 'application/x-msdownload');

        $this->expectException(ValidationException::class);
        $this->service->store($uploader, $bad, []);
    }

    public function test_store_requires_mediable_type_and_id_together(): void
    {
        $uploader = User::factory()->admin()->create();
        $file = UploadedFile::fake()->image('a.jpg', 10, 10);

        $this->expectException(ValidationException::class);
        $this->service->store($uploader, $file, [
            'mediable_id' => 1,
        ]);
    }

    public function test_store_rejects_project_not_found_when_mediable_set(): void
    {
        $uploader = User::factory()->admin()->create();
        $file = UploadedFile::fake()->image('a.jpg', 10, 10);

        $this->expectException(ValidationException::class);
        $this->service->store($uploader, $file, [
            'mediable_type' => Project::class,
            'mediable_id' => 999999,
        ]);
    }

    public function test_store_saves_media_and_file_on_public_disk(): void
    {
        $uploader = User::factory()->admin()->create();
        $project = Project::factory()->create();

        $file = UploadedFile::fake()->image('photo.jpg', 1200, 900);

        $media = $this->service->store($uploader, $file, [
            'collection' => 'default',
            'mediable_type' => Project::class,
            'mediable_id' => $project->id,
            'alt_text_ar' => 'صورة',
        ]);

        $this->assertInstanceOf(Media::class, $media);
        $this->assertSame('public', $media->disk);
        $this->assertNotEmpty($media->path);
        $this->assertTrue(Storage::disk('public')->exists($media->path));
    }
}
