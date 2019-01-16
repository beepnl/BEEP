<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Zizaco\Entrust\Traits\EntrustUserTrait;

use App\Notifications\VerifyEmail;
use App\Notifications\ResetPassword;

use DB;

class User extends Authenticatable
{
    use Notifiable;
    use EntrustUserTrait;

    protected $fillable = ['name', 'email', 'password', 'api_token', 'last_login', 'policy_accepted'];

    protected $hidden = ['password', 'remember_token'];

    protected $guarded  = ['id'];

    //protected $cascadeDeletes = ['hives','checklists','inspections','locations','sensors']; // for soft deletes

    //protected $appends  = ['inspectioncount'];


    // links
    public function hives()
    {
        return $this->hasMany(Hive::class);
    }

    public function allHhives() // Including Group hives
    {
        $own_ids = $this->hives()->pluck('id');
        $grp_ids = $this->groupHives()->pluck('id');
        $all_ids = $own_ids->merge($grp_ids);
        return Hive::whereIn('id',$all_ids);
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

    // TODO: Add GUI for attaching sensor rights to users
    public function sensors()
    {
        return $this->hasMany(Sensor::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_user')->whereNotNull('accepted');
    }

    public function groupHives()
    {
        $group_ids = $this->groups->pluck('id')->toArray();
        $hive_ids  = DB::table('group_hive')->whereIn('group_id',$group_ids)->distinct('hive_id')->pluck('hive_id')->toArray();
        //die(print_r(['group_ids'=>$group_ids,'hive_ids'=>$hive_ids]));
        return Hive::whereIn('id',$hive_ids);
    }

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
