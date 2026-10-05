<?php

namespace App;

use Auth;
use Cache;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

#[WithoutTimestamps]
#[Fillable('user_id', 'continent_id', 'category_id', 'name', 'coordinate_lat', 'coordinate_lon', 'street', 'street_no', 'postal_code', 'country_code', 'city', 'roofed', 'last_weather_time', 'hex_color')]
#[Guarded('id')]
#[Hidden('user_id', 'continent_id', 'category_id')]
#[Appends('type', 'continent', 'owner')]
class Location extends Model
{
    use CascadeSoftDeletes, SoftDeletes;
    use HasFactory;

    protected $cascadeDeletes = ['hives', 'inspections'];

    // Caching
    public static function boot()
    {
        parent::boot();

        static::created(function ($l) {
            $l->empty_cache();
        });

        static::updated(function ($l) {
            if ($l->wasChanged('last_weather_time') == false) { // do not empty cache by only weather time change
                $l->empty_cache();
            }
        });

        static::deleted(function ($l) {
            $l->empty_cache();
        });
    }

    // Cache functions
    public function empty_cache($clear_user = true)
    {
        Log::debug("Location ID $this->id cache emptied");

        if ($clear_user) {
            User::emptyIdCache($this->user_id, 'apiary');
        }
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
        if ($this->user_id == Auth::user()->id) {
            return true;
        }

        return false;
    }

    public function hives(): HasMany
    {
        return $this->hasMany(Hive::class);
    }

    public function inspections(): BelongsToMany
    {
        return $this->belongsToMany(Inspection::class, 'inspection_location');
    }

    public function layers(): HasManyThrough
    {
        return $this->hasManyThrough(HiveLayer::class, Hive::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function device_count(): HasManyThrough
    {
        return $this->hasManyThrough(Device::class, Hive::class)->count();
    }

    public function continent(): HasOne
    {
        return $this->hasOne(Continent::class);
    }

    public static function selectList()
    {
        return Location::orderBy('name')->pluck('name', 'id');
    }
}
