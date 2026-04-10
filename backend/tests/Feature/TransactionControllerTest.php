<?php

namespace Tests\Feature\Api\V1;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_own_transactions()
    {
        $user = User::factory()->create();
        Transaction::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->getJson('/api/v1/transactions');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_list_all_transactions_as_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        Transaction::factory()->create(['user_id' => $user->id]);
        Transaction::factory()->create(['user_id' => $admin->id]);

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/transactions');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_show_own_transaction()
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->getJson("/api/v1/transactions/{$transaction->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $transaction->id);
    }

    public function test_cannot_view_others_transaction()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $transaction = Transaction::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)
            ->getJson("/api/v1/transactions/{$transaction->id}");

        $response->assertStatus(403);
    }

    public function test_filter_transactions_by_type()
    {
        $user = User::factory()->create();
        Transaction::factory()->create(['user_id' => $user->id, 'type' => 'payment']);
        Transaction::factory()->create(['user_id' => $user->id, 'type' => 'withdrawal']);

        $response = $this->actingAs($user)
            ->getJson('/api/v1/transactions?type=payment');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
