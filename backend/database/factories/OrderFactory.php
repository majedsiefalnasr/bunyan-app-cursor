<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'customer_id' => User::factory(),
            'project_id' => null,
            'status' => 'pending',
            'total_amount' => fake()->randomFloat(2, 10, 5000),
            'notes' => null,
            'delivery_date' => null,
            'delivery_address' => null,
        ];
    }
}
