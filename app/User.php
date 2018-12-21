<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Zizaco\Entrust\Traits\EntrustUserTrait;

use App\Notifications\VerifyEmail;
use App\Notifications\ResetPassword;

class User extends Authenticatable
{
    use Notifiable;
    use EntrustUserTrait;

    protected $fillable = ['name', 'email', 'password', 'api_token', 'last_login', 'policy_accepted'];

    protected $hidden = ['password', 'remember_token'];

    protected $guarded  = ['id'];

    //protected $cascadeDeletes = ['hives','checklists','inspections','locations','sensors']; // for soft deletes

    //protected $appends  = ['inspectioncount'];


    public function inspectionCount()
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

    public function checklists()
    {
        return $this->belongsToMany(Checklist::class, 'checklist_user');
    }

    public function inspections()
    {
        return $this->belongsToMany(Inspection::class, 'inspection_user');
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
        return $this->hasManyThrough(Action::class, Hive::class);
    }

    // TODO: Add GUI for attaching sensor rights to users
    public function sensors()
    {
        return $this->hasMany(Sensor::class);
    }

    // public function groups()
    // {
    //     return $this->belongsToMany(Group::class,'group_user');
    // }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }

    public function inspectionDates()
    {
        $inspections = 0;

        if (count($this->hives) > 0)
        {
            foreach($this->hives as $hive)
            {
                $inspections += $hive->inspectionDates()->count();
            }
        }
        return $inspections;
    }

    public function delete()
    {
        // delete all related photos 
        $this->checklists()->delete();

        // delete the user
        return parent::delete();
    }

    public function sendApiEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail); // my notification
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token)); // my notification
    }
}
