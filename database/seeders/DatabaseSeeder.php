<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(DeviceCorrectionSeeder::class);
        // $this->call(UserSeeder::class);
        // $this->call(MeasurementSeeder::class);
    }
}
