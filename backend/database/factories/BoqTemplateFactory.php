<?php

namespace Database\Factories;

use App\Models\BoqTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BoqTemplate>
 */
class BoqTemplateFactory extends Factory
{
    protected $model = BoqTemplate::class;

    public function definition(): array
    {
        return [
            'name_ar' => fake()->words(2, true),
            'name_en' => fake()->words(2, true),
            'project_type' => 'residential',
            'items_json' => [
                ['description' => 'Concrete', 'unit' => 'm3', 'qty' => 10],
            ],
            'created_by' => User::factory(),
        ];
    }
}
