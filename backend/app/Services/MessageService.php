<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Repositories\MessageRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MessageService
{
    public function __construct(private MessageRepository $messages)
    {
    }

    /**
     * @param  array{body?: ?string, type?: string}  $data
     */
    public function send(User $sender, Conversation $conversation, array $data, ?UploadedFile $attachment = null): Message
    {
        $body = isset($data['body']) ? trim((string) $data['body']) : '';
        $hasFile = $attachment !== null && $attachment->isValid();

        if ($body === '' && ! $hasFile) {
            throw ValidationException::withMessages([
                'body' => ['أدخل نصاً أو أرفق ملفاً'],
            ]);
        }

        return DB::transaction(function () use ($sender, $conversation, $body, $hasFile, $attachment) {
            $attachmentPath = null;
            $type = 'text';

            if ($hasFile) {
                $type = 'file';
            }

            /** @var Message $message */
            $message = $this->messages->create([
                'conversation_id' => $conversation->id,
                'sender_id' => $sender->id,
                'body' => $body !== '' ? $body : null,
                'type' => $type,
                'attachment_path' => null,
            ]);

            if ($hasFile && $attachment !== null) {
                $path = $attachment->store('messages/'.$message->id, 'public');
                $message->update(['attachment_path' => $path]);
            }

            $conversation->touch();

            $message->refresh();
            $message->load('sender');
            broadcast(new MessageSent($message))->toOthers();

            return $message;
        });
    }

    public function attachmentPublicUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
