<?php

namespace Database\Factories;

use App\Models\ModelShowroom;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ModelShowroom> */
class ModelShowroomFactory extends Factory
{
    protected $model = ModelShowroom::class;

    public function definition(): array
    {
        return [
            'model_name' => fake()->unique()->bothify('Q##'),
            'year' => 2014,
            'price' => fake()->numberBetween(30000, 70000),
            'image' => null,
            'status' => true,
            'display_order' => fake()->numberBetween(1, 50),
        ];
    }

    public function inactive(): static
    {
        return $this->state(['status' => false]);
    }
}
