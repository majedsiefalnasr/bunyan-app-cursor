<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessagingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_direct_conversation(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $response = $this->actingAs($a)->postJson('/api/v1/conversations', [
            'type' => 'direct',
            'participant_ids' => [$b->id],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.type', 'direct');
    }

    public function test_user_can_list_conversations(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $this->actingAs($a)->postJson('/api/v1/conversations', [
            'type' => 'direct',
            'participant_ids' => [$b->id],
        ])->assertStatus(201);

        $list = $this->actingAs($a)->getJson('/api/v1/conversations');
        $list->assertStatus(200)->assertJsonPath('success', true);
        $payload = $list->json('data');
        $items = is_array($payload) && array_key_exists('data', $payload) ? $payload['data'] : $payload;
        $this->assertIsArray($items);
        $this->assertNotEmpty($items);
    }

    public function test_participant_can_send_and_list_messages(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $c = $this->actingAs($a)->postJson('/api/v1/conversations', [
            'type' => 'direct',
            'participant_ids' => [$b->id],
        ]);
        $conversationId = $c->json('data.id');

        $send = $this->actingAs($a)->postJson("/api/v1/conversations/{$conversationId}/messages", [
            'body' => 'مرحباً',
        ]);
        $send->assertStatus(201)->assertJsonPath('data.body', 'مرحباً');

        $index = $this->actingAs($b)->getJson("/api/v1/conversations/{$conversationId}/messages");
        $index->assertStatus(200);
        $payload = $index->json('data');
        $items = is_array($payload) && array_key_exists('data', $payload) ? $payload['data'] : $payload;
        $this->assertIsArray($items);
        $this->assertNotEmpty($items);
    }

    public function test_non_participant_cannot_access_messages(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $stranger = User::factory()->create();

        $c = $this->actingAs($a)->postJson('/api/v1/conversations', [
            'type' => 'direct',
            'participant_ids' => [$b->id],
        ]);
        $conversationId = $c->json('data.id');

        $this->actingAs($stranger)->getJson("/api/v1/conversations/{$conversationId}/messages")
            ->assertStatus(404);
    }

    public function test_mark_read_succeeds(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $c = $this->actingAs($a)->postJson('/api/v1/conversations', [
            'type' => 'direct',
            'participant_ids' => [$b->id],
        ]);
        $conversationId = $c->json('data.id');

        $this->actingAs($b)->putJson("/api/v1/conversations/{$conversationId}/read")
            ->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}
