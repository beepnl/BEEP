<?php

use Illuminate\Database\Seeder;
use App\HiveFactory;

class HiveSeeder extends Seeder
{
    /**
     * @var HiveFactory
    **/
    private $hiveFactory;

    public function __construct(HiveFactory $hiveFactory)
    {
        $this->hiveFactory = $hiveFactory;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = App\User::all();
        $hive_amount = 3;
        $loca_amount = 2;
        $hive_suffix = "'s kast";
        $hive_type   = "spaarkast";
        $broodLayers = 2;
        $honeyLayers = 1;
        $layerFrames = 10;

        foreach ($users as $user) 
        {
            $locations = factory(App\Location::class, $loca_amount)->create(['user_id'=>$user->id]);

            foreach ($locations as $location) 
            {
                $this->hiveFactory->createMultipleHives($user->id, $hive_amount, $location, $user->name.$hive_suffix, $hive_type, null, $broodLayers, $honeyLayers, $layerFrames);
            }
        }
    }

    public function fakeHives() // not user in seeding
    {
        factory(App\Location::class, 3)->create()->each(function ($location) 
        {
            $location->hives()->saveMany(factory(App\Hive::class, 5)->create()->each(function ($hive) 
            {
                $hive->layers()->saveMany(factory(App\HiveLayer::class, 1)->states('brood')->create()->each(function ($layer) 
                {
                    $layer->frames()->saveMany(factory(App\HiveLayerFrame::class, 10)->make());
                }));
                $hive->layers()->saveMany(factory(App\HiveLayer::class, 1)->states('honey')->create()->each(function ($layer) 
                {
                    $layer->frames()->saveMany(factory(App\HiveLayerFrame::class, 10)->make());
                }));
            }));
        });
    }
}
