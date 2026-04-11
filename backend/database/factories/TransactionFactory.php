<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'project_id' => null,
            'order_id' => null,
            'type' => fake()->randomElement(['payment', 'withdrawal']),
            'amount' => fake()->randomFloat(2, 10, 5000),
            'status' => 'completed',
            'payment_method' => null,
            'reference' => null,
            'description' => null,
        ];
    }
}
