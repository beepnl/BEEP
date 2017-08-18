<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Zizaco\Entrust\Traits\EntrustUserTrait;

class User extends Authenticatable
{
    use Notifiable;
    use EntrustUserTrait;

    protected $fillable = ['name', 'email', 'password', 'api_token', 'last_login'];

    protected $hidden = ['password', 'remember_token'];

    protected $guarded  = ['id'];

    //protected $appends  = ['inspectioncount'];


    public function getInspectioncountAttribute()
    {
        $actions    = $this->actions()->count();
        $conditions = $this->conditions()->count();
        return max($actions, $conditions);
    }

    // links
    public function hives()
    {
        return $this->hasMany(Hive::class);
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function conditions()
    {
        return $this->hasManyThrough(Condition::class, Hive::class);
    }

    public function actions()
    {
        return $this->hasManyThrough(Condition::class, Hive::class);
    }

    public function sensors()
    {
        return $this->hasMany(Sensor::class);
    }
    // TODO: Add GUI for attaching sensor rights to users
    // public function sensors()
    // {
    //     return $this->belongsToMany(Sensor::class,'sensor_user');
    // }

    // public function groups()
    // {
    //     return $this->belongsToMany(Group::class,'group_user');
    // }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }
}
