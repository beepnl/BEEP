<?php

namespace App;

use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Queen extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    protected $fillable = ['hive_id', 'created_at', 'race_id', 'quality', 'color', 'name', 'fertilized', 'clipped', 'fertilizing_location', 'origin', 'tree', 'line', 'mother_id', 'marker', 'goal', 'description', 'birth_date'];
	protected $guarded 	= ['id'];
    protected $hidden   = ['fertilizing_location', 'marker', 'goal', 'deleted_at', 'laravel_through_key', 'created_at'];
    protected $appends  = ['race'];

    public $timestamps = false;

    // Relations
    public function getRaceAttribute()
    {
        return isset($this->race_id) && $this->race_id != '' ? Category::find($this->race_id) != null ? Category::find($this->race_id)->name : '' : '';
    }

    public function getMotherAttribute()
    {
        return isset($this->mother_id) ? Queen::find($this->mother_id)->name : '';
    }

	public function hive()
    {
        return $this->belongsTo(Hive::class);
    }

    public function race()
    {
        return $this->hasOne(Category::class, 'race_id');
    }

    public function mother()
    {
        return $this->hasOne(Queen::class, 'mother_id');
    }

    public static function selectList()
    {
        return Queen::orderBy('name')->pluck('name','id');
    }
}
