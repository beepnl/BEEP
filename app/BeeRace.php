<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BeeRace extends Model
{
    protected $fillable = ['name', 'type', 'synonyms', 'continents'];
	protected $guarded 	= ['id'];

}
