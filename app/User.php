<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Zizaco\Entrust\Traits\EntrustUserTrait;

use App\Notifications\VerifyEmail;
use App\Notifications\ResetPassword;

use App\Models\Alert;
use App\Models\AlertRule;

use DB;

class User extends Authenticatable
{
    use Notifiable;
    use EntrustUserTrait;

    protected $fillable = ['name', 'email', 'password', 'api_token', 'last_login', 'policy_accepted', 'locale'];

    protected $hidden = ['password', 'remember_token', 'researchesVisible', 'researchesOwned'];

    protected $guarded  = ['id'];

    //protected $cascadeDeletes = ['hives','checklists','inspections','locations','sensors']; // for soft deletes

    protected $appends  = ['app_debug'];


    public function getAppDebugAttribute()
    {
        return $this->can('app-debug');
    }

    // links
    public function images()
    {
        return $this->hasMany(Image::class);
    }

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

    public function researches()
    {
        return $this->belongsToMany(Research::class, 'research_user');
    }
    
    public function researchesOwned()
    {
        return $this->hasMany(Research::class);
    }

    public function researchesVisible()
    {
        return $this->belongsToMany(Research::class, 'research_viewer');
    }
    

    public function researchMenuOption()
    {
        if ($this->hasRole('superadmin'))
            return true;
        
        if ($this->researchesOwned && $this->researchesOwned->count() > 0)
            return true;

        if ($this->researchesVisible && $this->researchesVisible->count() > 0)
            return true;

        return false;
    }

    public function allResearches() // all Researches visible
    {
        $own_ids = $this->researchesOwned()->pluck('id');
        $vis_ids = $this->researchesVisible()->pluck('research_id');
        $all_ids = $own_ids->merge($vis_ids);
        return Research::whereIn('id',$all_ids);
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_user')->whereNotNull('accepted');
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }

    public function samplecodes()
    {
        return $this->hasMany(SampleCode::class);
    }

    public function queens()
    {
        return $this->hasManyThrough(Queen::class, Hive::class, 'user_id', 'hive_id');
    }

    public function researchChecklists()
    {
        $research_ids = $this->researches->pluck('id')->toArray();

        $checklist_ids = DB::table('checklist_research')->whereIn('research_id',$research_ids)->distinct('checklist_id')->pluck('checklist_id')->toArray();
        //die(print_r(['group_ids'=>$group_ids,'checklist_ids'=>$checklist_ids]));
        return Checklist::whereIn('id',$checklist_ids);
    }

    public function allChecklists()
    {
        $own_checklists = $this->checklists()->pluck('id');
        $research_cl    = $this->researchChecklists()->pluck('id');
        $checklist_ids  = $own_checklists->merge($research_cl); 
        
        return Checklist::whereIn('id', $checklist_ids);
    }

    public function groupHives($editable = false)
    {
        $group_ids = $this->groups->pluck('id')->toArray();

        $hive_ids  = [];
        if ($editable)
            $hive_ids  = DB::table('group_hive')->where('edit_hive', 1)->whereIn('group_id',$group_ids)->distinct('hive_id')->pluck('hive_id')->toArray();
        else
            $hive_ids  = DB::table('group_hive')->whereIn('group_id',$group_ids)->distinct('hive_id')->pluck('hive_id')->toArray();
        //die(print_r(['group_ids'=>$group_ids,'hive_ids'=>$hive_ids]));
        return Hive::whereIn('id',$hive_ids);
    }

    public function allHives($editable = false) // Including Group hives
    {
        $own_ids = $this->hives()->pluck('id');
        $hiv_ids = $this->groupHives($editable)->pluck('id');
        $all_ids = $own_ids->merge($hiv_ids);
        return Hive::whereIn('id',$all_ids);
    }

    public function allInspections($editable = false) // Including Group hive locations
    {
        $own_ids = $this->inspections()->pluck('id');
        $hiv_ids = $this->groupHives($editable)->pluck('id');
        $ins_ids = DB::table('inspection_hive')->whereIn('hive_id',$hiv_ids)->distinct('inspection_id')->pluck('inspection_id')->toArray();
        $all_ids = $own_ids->merge($ins_ids);
        return Inspection::whereIn('id',$all_ids);
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function allLocations($editable = false) // Including Group hive locations
    {
        $own_ids = $this->locations()->pluck('id');
        $hiv_ids = $this->groupHives($editable)->pluck('id');
        $loc_ids = DB::table('hives')->whereIn('id',$hiv_ids)->distinct('hive_id')->pluck('location_id')->toArray();
        $all_ids = $own_ids->merge($loc_ids);
        return Location::whereIn('id',$all_ids);
    }

    
    public function allDevices($editable = false) // Including Group hive locations
    {
        $own_ids = $this->devices()->pluck('id');
        $hiv_ids = $this->groupHives($editable)->pluck('id');
        $sen_ids = DB::table('sensors')->whereIn('hive_id',$hiv_ids)->distinct('hive_id')->pluck('id')->toArray();
        $all_ids = $own_ids->merge($sen_ids);
        return Device::whereIn('id',$all_ids);
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

    public function alert_rules()
    {
        return $this->hasMany(AlertRule::class);
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class);
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

    public static function selectList()
    {
        return User::orderBy('name')->pluck('name','id');
    }
}
