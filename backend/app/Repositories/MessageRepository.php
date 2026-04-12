<?php

namespace App\Repositories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class MessageRepository extends BaseRepository
{
    protected function model(): string
    {
        return Message::class;
    }

    public function paginateForConversation(int $conversationId, int $perPage = 30): LengthAwarePaginator
    {
        return $this->newQuery()
            ->where('conversation_id', $conversationId)
            ->with('sender')
            ->orderByDesc('id')
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
