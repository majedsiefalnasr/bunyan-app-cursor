<?php

namespace App\Repositories;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class ConversationRepository extends BaseRepository
{
    protected function model(): string
    {
        return Conversation::class;
    }

    public function paginateForUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->newQuery()
            ->whereHas('participants', fn (Builder $q) => $q->where('user_id', $user->id))
            ->with([
                'participants.user',
                'latestMessage.sender',
                'project',
            ])
            ->orderByDesc('updated_at')
            ->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Model
    {
        return parent::create($data);
    }
}
