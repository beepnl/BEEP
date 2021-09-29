<?php

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

$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => Str::random(10),
        'api_token' => Str::random(60),
    ];
});

$factory->define(App\Location::class, function (Faker\Generator $faker) {

    return [
    	'user_id' => 1, 
    	'continent_id' => 4, // eu
    	'category_id' => 36, // fixed
    	'name' => $faker->name, 
    	'coordinate_lat' => $faker->latitude, 
    	'coordinate_lon' => $faker->longitude, 
    	'street' => $faker->streetName, 
    	'street_no' => $faker->numberBetween($min=1, $max=99), 
    	'postal_code' => $faker->postcode, 
    	'country_code' => $faker->country,
    ];
});

$factory->define(App\Hive::class, function (Faker\Generator $faker) {

    return [
        'user_id' => 1, 
        'location_id' => 1, 
        'hive_type_id' => 1, 
        'color' => $faker->hexcolor, 
        'name' => $faker->name,
    ];
});

$factory->define(App\HiveLayer::class, function (Faker\Generator $faker) {

    return [
        'hive_id' => 1, 
        'category_id' => 26, 
        'order' => $faker->numberBetween($min=1, $max=99), 
        'color' => $faker->hexcolor, 
    ];
});

$factory->state(App\HiveLayer::class, 'brood', function ($faker) {
    return [
        'category_id' => 26,
    ];
});

$factory->state(App\HiveLayer::class, 'honey', function ($faker) {
    return [
        'category_id' => 27,
    ];
});

$factory->define(App\HiveLayerFrame::class, function (Faker\Generator $faker) {

    return [
        'layer_id' => 1, 
        'category_id' => 29, // wax
        'order' => $faker->numberBetween($min=1, $max=99), 
        'present' => true, 
    ];
});