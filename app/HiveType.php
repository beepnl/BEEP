<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HiveType extends Model
{
    use SoftDeletes;

    protected $fillable = ['hive_id', 'type', 'name', 'image', 'continents', 'info_url'];
	protected $guarded 	= ['id'];

}
