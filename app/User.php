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

    public function checklists()
    {
        return $this->belongsToMany(Checklist::class, 'checklist_user');
    }

    public function inspections()
    {
        return $this->belongsToMany(Inspection::class, 'inspection_user');
    }
    
    public function sensors()
    {
        return $this->hasMany(Sensor::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_user')->whereNotNull('accepted');
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }


    public function groupHives($mine = false)
    {
        $group_ids = $this->groups->pluck('id')->toArray();

        $hive_ids  = [];
        if ($mine)
            $hive_ids  = DB::table('group_hive')->where('edit_hive', 1)->whereIn('group_id',$group_ids)->distinct('hive_id')->pluck('hive_id')->toArray();
        else
            $hive_ids  = DB::table('group_hive')->whereIn('group_id',$group_ids)->distinct('hive_id')->pluck('hive_id')->toArray();
        //die(print_r(['group_ids'=>$group_ids,'hive_ids'=>$hive_ids]));
        return Hive::whereIn('id',$hive_ids);
    }

    public function allHives($mine = false) // Including Group hives
    {
        $own_ids = $this->hives()->pluck('id');
        $hiv_ids = $this->groupHives($mine)->pluck('id');
        $all_ids = $own_ids->merge($hiv_ids);
        return Hive::whereIn('id',$all_ids);
    }

    public function allInspections($mine = false) // Including Group hive locations
    {
        $own_ids = $this->inspections()->pluck('id');
        $hiv_ids = $this->groupHives($mine)->pluck('id');
        $ins_ids = DB::table('inspection_hive')->whereIn('hive_id',$hiv_ids)->distinct('inspection_id')->pluck('inspection_id')->toArray();
        $all_ids = $own_ids->merge($ins_ids);
        return Inspection::whereIn('id',$all_ids);
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function allLocations($mine = false) // Including Group hive locations
    {
        $own_ids = $this->locations()->pluck('id');
        $hiv_ids = $this->groupHives($mine)->pluck('id');
        $loc_ids = DB::table('hives')->whereIn('id',$hiv_ids)->distinct('hive_id')->pluck('location_id')->toArray();
        $all_ids = $own_ids->merge($loc_ids);
        return Location::whereIn('id',$all_ids);
    }

    
    public function allSensors($mine = false) // Including Group hive locations
    {
        $own_ids = $this->sensors()->pluck('id');
        $hiv_ids = $this->groupHives($mine)->pluck('id');
        $sen_ids = DB::table('sensors')->whereIn('hive_id',$hiv_ids)->distinct('hive_id')->pluck('id')->toArray();
        $all_ids = $own_ids->merge($sen_ids);
        return Sensor::whereIn('id',$all_ids);
    }



    public function groupInvitations()
    {
        $user_id = $this->id;
        return $this->belongsToMany(Group::class, 'group_user')->whereNotNull('invited')->whereNull('accepted')->get()->map(function ($item, $key) use ($user_id)
        {
            $invite              = $item->only(['id','name','description','color']);
            $groupUserArray      = $item->users->firstWhere('id',$user_id);
            $invite['invited']   = (isset($groupUserArray['invited'])) ? $item->users->firstWhere('id',$user_id)['invited'] : null;
            $invite['token']     = (isset($groupUserArray['token'])) ? $item->users->firstWhere('id',$user_id)['token'] : null;
            $invite['hivecount'] = $item->hives->count();
            $invite['usercount'] = $item->users->count();
            return $invite; 
        });
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
