<?php

namespace Database\Factories;

use App\Enums\SupplierVerificationStatus;
use App\Models\SupplierProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplierProfile>
 */
class SupplierProfileFactory extends Factory
{
    protected $model = SupplierProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->contractor(),
            'company_name_ar' => fake()->company(),
            'company_name_en' => fake()->optional()->company(),
            'commercial_reg' => fake()->optional()->numerify('########'),
            'tax_number' => fake()->optional()->numerify('3#########'),
            'city' => fake()->city(),
            'district' => fake()->optional()->streetName(),
            'address' => fake()->optional()->address(),
            'phone' => fake()->optional()->numerify('+9665#######'),
            'verification_status' => SupplierVerificationStatus::Pending->value,
            'verified_at' => null,
            'rating_avg' => 0,
            'total_ratings' => 0,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => SupplierVerificationStatus::Verified->value,
            'verified_at' => now(),
        ]);
    }
}
