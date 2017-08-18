<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(LocationSeeder::class);
        $this->call(BeeRaceSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(HiveTypeSeeder::class);
        $this->call(PermissionTableSeeder::class);
        $this->call(AdminSeeder::class);
        //$this->call(HiveSeeder::class);
        //$this->call(SensorSeeder::class);
    }
}
