<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Project;
use App\Models\User;
use App\Repositories\ConversationRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ConversationService
{
    public function __construct(private ConversationRepository $conversations)
    {
    }

    public function paginateForUser(User $user, int $perPage): LengthAwarePaginator
    {
        return $this->conversations->paginateForUser($user, $perPage);
    }

    /**
     * @param  array{type: string, title?: ?string, project_id?: ?int, participant_ids: array<int>}  $data
     */
    public function create(User $creator, array $data): Conversation
    {
        $type = $data['type'];
        $participantIds = array_values(array_unique(array_map('intval', $data['participant_ids'])));

        if (in_array($creator->id, $participantIds, true)) {
            throw ValidationException::withMessages([
                'participant_ids' => ['لا يمكن تضمين نفسك في قائمة المشاركين'],
            ]);
        }

        $othersCount = count($participantIds);
        if ($type === 'direct') {
            if ($othersCount !== 1) {
                throw ValidationException::withMessages([
                    'participant_ids' => ['المحادثة المباشرة تتطلب مشاركاً واحداً بالضبط'],
                ]);
            }
        } elseif ($type === 'group') {
            if ($othersCount < 2) {
                throw ValidationException::withMessages([
                    'participant_ids' => ['محادثة المجموعة تتطلب مشاركين إضافيين على الأقل'],
                ]);
            }
            if (empty($data['title'])) {
                throw ValidationException::withMessages([
                    'title' => ['عنوان المجموعة مطلوب'],
                ]);
            }
        } else {
            throw ValidationException::withMessages([
                'type' => ['نوع المحادثة غير صالح'],
            ]);
        }

        $existingUsers = User::query()->whereIn('id', $participantIds)->pluck('id');
        if ($existingUsers->count() !== $othersCount) {
            throw ValidationException::withMessages([
                'participant_ids' => ['أحد المستخدمين غير موجود'],
            ]);
        }

        if (! empty($data['project_id'])) {
            $project = Project::query()->findOrFail((int) $data['project_id']);
            Gate::forUser($creator)->authorize('view', $project);
        }

        return DB::transaction(function () use ($creator, $data, $type, $participantIds) {
            /** @var Conversation $conversation */
            $conversation = $this->conversations->create([
                'project_id' => $data['project_id'] ?? null,
                'title' => $data['title'] ?? null,
                'type' => $type,
            ]);

            $now = now();
            $rows = [];
            $allIds = array_merge([$creator->id], $participantIds);
            foreach ($allIds as $uid) {
                $rows[] = [
                    'conversation_id' => $conversation->id,
                    'user_id' => $uid,
                    'last_read_at' => $uid === $creator->id ? $now : null,
                    'joined_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            ConversationParticipant::query()->insert($rows);

            return $conversation->fresh(['participants.user', 'project']);
        });
    }

    public function markRead(User $user, Conversation $conversation): void
    {
        ConversationParticipant::query()
            ->where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->update(['last_read_at' => now()]);
    }
}
