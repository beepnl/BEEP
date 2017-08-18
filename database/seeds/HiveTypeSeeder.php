<?php

use Illuminate\Database\Seeder;
use App\HiveType;

class HiveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $bee_hives = [
            'spaarkast'=>[
                'name'=>'Spaarkast', 
                'image'=>'spaarkast.svg', 
                'continents'=>'eu',
                'info_url'=>'http://www.imkerpedia.nl/wiki/index.php?title=Spaarkast'
            ],
            'segeberger'=>[
                'name'=>'Segeberger', 
                'image'=>'segeberger.svg', 
                'continents'=>'eu',
                'info_url'=>'http://www.imkerpedia.nl/wiki/index.php?title=Segeberger'
            ],
            'dadant'=>[
                'name'=>'Dadant', 
                'image'=>'dadant.svg', 
                'continents'=>'eu',
                'info_url'=>'http://www.imkerpedia.nl/wiki/index.php?title=Dadantkast'
            ],
            'langstroth'=>[
                'name'=>'Langstroth', 
                'image'=>'langstroth.svg', 
                'continents'=>'eu,na',
                'info_url'=>'https://en.wikipedia.org/wiki/Langstroth_hive'
            ],
            'topbar'=>[
                'name'=>'Top-bar', 
                'image'=>'topbar.svg', 
                'continents'=>'af',
                'info_url'=>'https://en.wikipedia.org/wiki/Horizontal_top-bar_hive'
            ],
            'simplex'=>[
                'name'=>'Simplex', 
                'image'=>'simplex.svg', 
                'continents'=>'eu',
                'info_url'=>'http://www.imkerpedia.nl/wiki/index.php?title=Simplexkast'
            ],
            'skep'=>[
                'name'=>'Skep', 
                'image'=>'skep.svg', 
                'continents'=>'eu',
                'info_url'=>'https://en.wikipedia.org/wiki/Beehive#Skeps'
            ],
            'wbc'=>[
                'name'=>'William Broughton Carr', 
                'image'=>'wbc.svg', 
                'continents'=>'eu',
                'info_url'=>'http://www.imkerpedia.nl/wiki/index.php?title=WBC-kast'
            ],
        ]; 
        
        
        foreach ($bee_hives as $key => $value)
        { 
            $type             = new HiveType;
            $type->type       = $key;
            $type->name       = $value['name'];
            $type->image      = $value['image'];
            $type->continents = $value['continents'];
            $type->info_url   = $value['info_url'];
            $type->save();
        }
    }
}
