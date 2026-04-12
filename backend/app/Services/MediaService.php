<?php

namespace App\Services;

use App\Models\Media;
use App\Models\Project;
use App\Models\User;
use App\Repositories\MediaRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MediaService
{
    private const ALLOWED_MIMES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'application/pdf',
        'video/mp4',
    ];

    private const ALLOWLISTED_MEDIABLE = [
        Project::class,
    ];

    public function __construct(private MediaRepository $mediaRepository)
    {
    }

    /**
     * @param  array{collection?: string, alt_text_ar?: ?string, alt_text_en?: ?string, mediable_type?: ?string, mediable_id?: ?int, is_temporary?: bool, sort_order?: int}  $meta
     */
    public function store(User $uploader, UploadedFile $file, array $meta): Media
    {
        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                'file' => ['الملف غير صالح'],
            ]);
        }

        $mime = (string) $file->getMimeType();
        if (! in_array($mime, self::ALLOWED_MIMES, true)) {
            throw ValidationException::withMessages([
                'file' => ['نوع الملف غير مسموح'],
            ]);
        }

        $maxBytes = str_starts_with($mime, 'video/') ? 50 * 1024 * 1024 : 15 * 1024 * 1024;
        if ($file->getSize() > $maxBytes) {
            throw ValidationException::withMessages([
                'file' => ['حجم الملف يتجاوز الحد المسموح'],
            ]);
        }

        $mediableType = $meta['mediable_type'] ?? null;
        $mediableId = $meta['mediable_id'] ?? null;

        if ($mediableType !== null || $mediableId !== null) {
            if ($mediableType === null || $mediableId === null) {
                throw ValidationException::withMessages([
                    'mediable_id' => ['يجب إرسال نوع الكيان والمعرف معاً'],
                ]);
            }
            if (! in_array($mediableType, self::ALLOWLISTED_MEDIABLE, true)) {
                throw ValidationException::withMessages([
                    'mediable_type' => ['نوع الكيان غير مسموح'],
                ]);
            }
            $project = Project::query()->find($mediableId);
            if ($project === null) {
                throw ValidationException::withMessages([
                    'mediable_id' => ['المشروع غير موجود'],
                ]);
            }
            Gate::forUser($uploader)->authorize('view', $project);
        }

        $directory = 'media/'.Str::uuid()->toString();
        $path = $file->store($directory, 'public');
        $disk = 'public';

        $dimensions = $this->readDimensions($disk, $path, $mime);
        $thumbPath = $this->maybeCreateThumbnail($disk, $path, $mime, $directory);

        /** @var Media $media */
        $media = $this->mediaRepository->create([
            'mediable_type' => $mediableType,
            'mediable_id' => $mediableId,
            'collection' => $meta['collection'] ?? 'default',
            'filename' => basename($path),
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $mime,
            'disk' => $disk,
            'path' => $path,
            'thumb_path' => $thumbPath,
            'size_bytes' => (int) $file->getSize(),
            'dimensions_json' => $dimensions,
            'alt_text_ar' => $meta['alt_text_ar'] ?? null,
            'alt_text_en' => $meta['alt_text_en'] ?? null,
            'sort_order' => (int) ($meta['sort_order'] ?? 0),
            'uploaded_by' => $uploader->id,
            'is_temporary' => (bool) ($meta['is_temporary'] ?? false),
        ]);

        return $media;
    }

    public function delete(Media $media): void
    {
        $this->deleteStoredFiles($media);
        $this->mediaRepository->delete($media);
    }

    public function pruneTemporaryOlderThanHours(int $hours): int
    {
        $cutoff = now()->subHours($hours);
        $removed = 0;

        foreach ($this->mediaRepository->cursorTemporaryOlderThan($cutoff) as $media) {
            $this->deleteStoredFiles($media);
            $this->mediaRepository->delete($media);
            $removed++;
        }

        return $removed;
    }

    /**
     * @param  array{collection?: string|null, mime_type?: string|null, temporary?: bool|null, user_id?: int|null, per_page?: int|null, page?: int|null}  $filters
     */
    public function paginateForActor(User $actor, array $filters): LengthAwarePaginator
    {
        return $this->mediaRepository->paginateForActor($actor, $filters);
    }

    private function deleteStoredFiles(Media $media): void
    {
        $disk = $media->disk;
        if (Storage::disk($disk)->exists($media->path)) {
            Storage::disk($disk)->delete($media->path);
        }
        if ($media->thumb_path !== null && Storage::disk($disk)->exists($media->thumb_path)) {
            Storage::disk($disk)->delete($media->thumb_path);
        }
    }

    /**
     * @return array{width:int,height:int}|null
     */
    private function readDimensions(string $disk, string $path, string $mime): ?array
    {
        if (! str_starts_with($mime, 'image/')) {
            return null;
        }

        $full = Storage::disk($disk)->path($path);
        if (! is_file($full)) {
            return null;
        }

        $info = @getimagesize($full);
        if ($info === false) {
            return null;
        }

        return ['width' => (int) $info[0], 'height' => (int) $info[1]];
    }

    private function maybeCreateThumbnail(string $disk, string $path, string $mime, string $directory): ?string
    {
        if (! str_starts_with($mime, 'image/') || ! extension_loaded('gd')) {
            return null;
        }

        $full = Storage::disk($disk)->path($path);
        if (! is_file($full)) {
            return null;
        }

        try {
            $image = match ($mime) {
                'image/jpeg' => @imagecreatefromjpeg($full),
                'image/png' => @imagecreatefrompng($full),
                'image/gif' => @imagecreatefromgif($full),
                'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($full) : false,
                default => false,
            };
        } catch (\Throwable) {
            return null;
        }

        if ($image === false) {
            return null;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        $max = 400;
        if ($width <= $max && $height <= $max) {
            imagedestroy($image);

            return null;
        }

        $ratio = min($max / $width, $max / $height);
        $newW = (int) max(1, round($width * $ratio));
        $newH = (int) max(1, round($height * $ratio));

        $thumb = imagecreatetruecolor($newW, $newH);
        if ($thumb === false) {
            imagedestroy($image);

            return null;
        }

        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        imagecopyresampled($thumb, $image, 0, 0, 0, 0, $newW, $newH, $width, $height);
        imagedestroy($image);

        $thumbRelative = $directory.'/thumb_'.Str::uuid()->toString().'.jpg';
        $thumbFull = Storage::disk($disk)->path($thumbRelative);
        $dir = dirname($thumbFull);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (! imagejpeg($thumb, $thumbFull, 85)) {
            imagedestroy($thumb);

            return null;
        }

        imagedestroy($thumb);

        return $thumbRelative;
    }
}
