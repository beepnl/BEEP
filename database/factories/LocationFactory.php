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

class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => 1,
            'continent_id' => 4, // eu
            'category_id' => 36, // fixed
            'name' => $this->faker->name(),
            'coordinate_lat' => $this->faker->latitude(),
            'coordinate_lon' => $this->faker->longitude(),
            'street' => $this->faker->streetName(),
            'street_no' => $this->faker->numberBetween($min = 1, $max = 99),
            'postal_code' => $this->faker->postcode(),
            'country_code' => $this->faker->country(),
        ];
    }
}
