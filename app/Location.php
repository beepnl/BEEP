<?php

namespace App;

use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Cache;
use Auth;

class Location extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    protected $cascadeDeletes = ['hives', 'inspections'];

    protected $fillable = ['user_id', 'continent_id', 'category_id', 'name', 'coordinate_lat', 'coordinate_lon', 'street', 'street_no', 'postal_code', 'country_code', 'city', 'roofed', 'last_weather_time', 'hex_color'];
	protected $guarded 	= ['id'];
    protected $hidden   = ['user_id', 'continent_id', 'category_id'];
    protected $appends  = ['type', 'continent', 'owner'];

    public $timestamps = false;

    // Caching
    public static function boot()
    {
        parent::boot();

        static::created(function ($l) {
            $l->empty_cache();
        });

        static::updated(function ($l) {
            if ($l->wasChanged('last_weather_time') == false) // do not empty cache by only weather time change
                $l->empty_cache();
        });

        static::deleted(function ($l) {
            $l->empty_cache();
        });
    }

    // Cache functions
    public function empty_cache($clear_user=true)
    {
        Log::debug("Location ID $this->id cache emptied");

        if ($clear_user)
            User::emptyIdCache($this->user_id, 'apiary');
    }


    // Relations
    public function getTypeAttribute()
    {
        return Cache::rememberForever("location-type-$this->category_id-name", function () {
            return Category::find($this->category_id)->name;
        });
    }

    public function getContinentAttribute()
    {
        return Cache::rememberForever("continent-$this->continent_id-name", function () {
            return Continent::find($this->continent_id)->name;
        });
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

    public function device_count()
    {
        return $this->hasManyThrough(Device::class, Hive::class)->count();
    }

    public function continent()
    {
        return $this->hasOne(Continent::class);
    }

    public static function selectList()
    {
        return Location::orderBy('name')->pluck('name','id');
    }
}
