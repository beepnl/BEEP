<?php

namespace App;

use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Auth;

class Location extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    protected $cascadeDeletes = ['hives', 'inspections'];

    protected $fillable = ['user_id', 'continent_id', 'category_id', 'name', 'coordinate_lat', 'coordinate_lon', 'street', 'street_no', 'postal_code', 'country_code', 'city'];
	protected $guarded 	= ['id'];
    protected $hidden   = ['user_id', 'continent_id', 'category_id'];
    protected $appends  = ['type', 'continent', 'owner'];

    public $timestamps = false;

    // Relations
    public function getTypeAttribute()
    {
        return Category::find($this->category_id)->name;
    }

    public function getContinentAttribute()
    {
        return Continent::find($this->continent_id)->name;
    }

    public function getOwnerAttribute()
    {
        if ($this->user_id == Auth::user()->id)
            return true;
        
        return false;
    }

	public function hives()
    {
        return $this->hasMany(Hive::class);
    }

    public function inspections()
    {
        return $this->belongsToMany(Inspection::class, 'inspection_location');
    }

    public function layers()
    {
        return $this->hasManyThrough(HiveLayer::class, Hive::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function type()
    {
        return $this->belongsTo(Category::class);
    }

    public function sensors()
    {
        return $this->hasManyThrough(Sensor::class, Hive::class);
    }

    public function continent()
    {
        return $this->hasOne(Continent::class);
    }

}
