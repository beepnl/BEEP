<?php

use Illuminate\Database\Seeder;
use App\Continent;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $continents      = ['af','an','as','eu','na','oc','sa'];
        $continent_names = ['Africa','Antarctica','Asia','Europe','North America','Oceania','South and Central America'];

        for ($i=0; $i < count($continents); $i++) 
        { 
            $db = DB::table('continents')->insert(['name'=>$continent_names[$i], 'abbr'=>$continents[$i]]);
        }
    }
}
