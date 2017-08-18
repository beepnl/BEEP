<?php

use Illuminate\Database\Seeder;
use App\BeeRace;

class BeeRaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $bee_races = [
            'mellifera scutellata'=>[
                'name'=>'African', 
                'synonyms'=>'', 
                'continents'=>'af', 
                'info_url'=>'https://en.wikipedia.org/wiki/African_bee'
            ],
            'mellifera carnica'=>[
                'name'=>'Carnica', 
                'synonyms'=>'Carniolan', 
                'continents'=>'eu,na', 
                'info_url'=>'https://en.wikipedia.org/wiki/Carniolan_honeybee'
            ],
            'cerana'=>[
                'name'=>'Asiatic', 
                'synonyms'=>'Eastern', 
                'continents'=>'eu', 
                'info_url'=>'https://en.wikipedia.org/wiki/Apis_cerana'
            ],
            'mellifera adami'=>[
                'name'=>'Buckfast', 
                'synonyms'=>'Adami', 
                'continents'=>'eu,sa,na', 
                'info_url'=>'https://en.wikipedia.org/wiki/Buckfast_bee'
            ],
            'mellifera mellifera'=>[
                'name'=>'Dark', 
                'synonyms'=>'Black, Brown', 
                'continents'=>'eu,as,na', 
                'info_url'=>'https://en.wikipedia.org/wiki/European_dark_bee'
            ],
            'mellifera ligustica'=>[
                'name'=>'Italian', 
                'synonyms'=>'German', 
                'continents'=>'eu,na', 
                'info_url'=>'https://en.wikipedia.org/wiki/Italian_bee'
            ],
            'mellifera caucasica'=>[
                'name'=>'Caucasica', 
                'synonyms'=>'Pomonella', 
                'continents'=>'as,na', 
                'info_url'=>'https://en.wikipedia.org/wiki/Caucasian_honey_bee'
            ],
            'koschevnikovi'=>[
                'name'=>'Koschevnikovi', 
                'synonyms'=>'', 
                'continents'=>'as', 
                'info_url'=>'https://en.wikipedia.org/wiki/Apis_koschevnikovi'
            ],
            'africanized'=>[
                'name'=>'Africanised', 
                'synonyms'=>'Killer', 
                'continents'=>'na,sa', 
                'info_url'=>'https://en.wikipedia.org/wiki/Africanized_bee'
            ],
            'hybrid'=>[
                'name'=>'Hybrid', 
                'synonyms'=>'Cross breed', 
                'continents'=>null, 
                'info_url'=>''
            ],
            'other'=>[
                'name'=>'Other', 
                'synonyms'=>'', 
                'continents'=>null, 
                'info_url'=>''
            ],
        ]; // https://en.wikipedia.org/wiki/Honey_bee_race
        
        
        foreach ($bee_races as $key => $value)
        { 
            $race             = new BeeRace;
            $race->name       = $value['name'];
            $race->type       = $key;
            $race->synonyms   = $value['synonyms'];
            $race->continents = $value['info_url'];
            $race->save();
        }
    }
}
