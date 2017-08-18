<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Continent extends Model
{
    protected $fillable = ['name', 'abbr'];
	protected $guarded 	= ['id'];

}
