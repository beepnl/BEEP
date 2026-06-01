<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

class HiveLayerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'hive_id' => 1,
            'category_id' => 26,
            'order' => $this->faker->numberBetween($min = 1, $max = 99),
            'color' => $this->faker->hexColor(),
        ];
    }

    public function brood()
    {
        return $this->state(function () {
            return [
                'category_id' => 26,
            ];
        });
    }

    public function honey()
    {
        return $this->state(function () {
            return [
                'category_id' => 27,
            ];
        });
    }
}
