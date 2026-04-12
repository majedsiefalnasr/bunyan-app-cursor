<?php

namespace App\Services;

use App\Enums\DocumentCategory;
use App\Models\Document;
use App\Models\Project;
use App\Models\User;
use App\Repositories\DocumentRepository;
use App\Repositories\DocumentVersionRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DocumentService
{
    private const MAX_BYTES = 25 * 1024 * 1024;

    /** @var list<string> */
    private const ALLOWED_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    public function __construct(
        private DocumentRepository $documentRepository,
        private DocumentVersionRepository $documentVersionRepository,
    ) {
    }

    public function storeUpload(
        User $user,
        Project $project,
        UploadedFile $file,
        string $title,
        DocumentCategory $category,
        ?int $documentId,
    ): Document {
        $this->assertFileBasics($file);

        if ($documentId === null) {
            return $this->createFirstVersion($user, $project, $file, $title, $category);
        }

        $existing = $this->documentRepository->findForProjectOrFail($project, $documentId);

        return $this->appendVersion($user, $existing, $file);
    }

    private function createFirstVersion(
        User $user,
        Project $project,
        UploadedFile $file,
        string $title,
        DocumentCategory $category,
    ): Document {
        [$path, $mime, $size] = $this->storeBinary($file, null, 1);

        $document = $this->documentRepository->create([
            'documentable_type' => Project::class,
            'documentable_id' => $project->id,
            'category' => $category,
            'title' => $title,
            'original_filename' => $file->getClientOriginalName(),
            'storage_path' => $path,
            'mime_type' => $mime,
            'size_bytes' => $size,
            'version' => 1,
            'uploaded_by' => $user->id,
        ]);

        $this->documentVersionRepository->createForDocument($document, [
            'version' => 1,
            'storage_path' => $path,
            'size_bytes' => $size,
            'uploaded_by' => $user->id,
        ]);

        return $document->fresh(['uploadedBy']) ?? $document;
    }

    private function appendVersion(User $user, Document $document, UploadedFile $file): Document
    {
        $nextVersion = $document->version + 1;
        $directory = dirname($document->storage_path);
        [$path, $mime, $size] = $this->storeBinary($file, $directory, $nextVersion);

        $document->original_filename = $file->getClientOriginalName();
        $document->storage_path = $path;
        $document->mime_type = $mime;
        $document->size_bytes = $size;
        $document->version = $nextVersion;
        $document->uploaded_by = $user->id;
        $this->documentRepository->save($document);

        $this->documentVersionRepository->createForDocument($document, [
            'version' => $nextVersion,
            'storage_path' => $path,
            'size_bytes' => $size,
            'uploaded_by' => $user->id,
        ]);

        return $document->fresh(['uploadedBy']) ?? $document;
    }

    /**
     * @return array{0: string, 1: string, 2: int}
     */
    private function storeBinary(UploadedFile $file, ?string $directory, int $versionNumber): array
    {
        $mime = (string) $file->getMimeType();
        if (! in_array($mime, self::ALLOWED_MIMES, true)) {
            throw ValidationException::withMessages([
                'file' => ['نوع الملف غير مسموح'],
            ]);
        }

        $baseDir = $directory ?? ('documents/'.Str::uuid()->toString());
        $safeBase = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'file';
        $ext = strtolower((string) $file->getClientOriginalExtension());
        $storedName = 'v'.$versionNumber.'_'.$safeBase.($ext !== '' ? '.'.$ext : '');
        $path = $file->storeAs($baseDir, $storedName, 'public');

        return [$path, $mime, (int) $file->getSize()];
    }

    private function assertFileBasics(UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                'file' => ['الملف غير صالح'],
            ]);
        }

        if ($file->getSize() > self::MAX_BYTES) {
            throw ValidationException::withMessages([
                'file' => ['حجم الملف يتجاوز الحد المسموح (25MB)'],
            ]);
        }
    }

    public function softDelete(Document $document): void
    {
        $document->delete();
    }

    public function listVersions(Document $document): array
    {
        return $this->documentVersionRepository->listForDocument($document);
    }

    public function downloadResponse(Document $document)
    {
        if ($document->trashed()) {
            abort(404);
        }

        return Storage::disk('public')->download(
            $document->storage_path,
            $document->original_filename,
        );
    }
}
