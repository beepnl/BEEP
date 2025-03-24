<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Log;
use Laratrust\Traits\LaratrustUserTrait;

use App\Notifications\VerifyEmail;
use App\Notifications\ResetPassword;

use App\Models\DashboardGroup;
use App\Models\HiveTag;
use App\Models\FlashLog;
use App\Models\Alert;
use App\Models\AlertRule;
use App\Models\ChecklistSvg;

use DB;
use Auth;
use Cache;

class User extends Authenticatable
{
    use Notifiable;
    use LaratrustUserTrait;

    protected $fillable = ['name', 'email', 'password', 'api_token', 'last_login', 'policy_accepted', 'locale', 'avatar', 'rate_limit_per_min'];

    protected $hidden = ['password', 'remember_token', 'researchesVisible', 'researchesOwned'];

    protected $guarded  = ['id'];

    //protected $cascadeDeletes = ['hives','checklists','inspections','locations','sensors']; // for soft deletes

    protected $appends  = ['app_debug', 'admin', 'permissions'];


    // Fix for Trebol\Entrust permissions that do not check 
    // public function can($permission, $arguments=[])
    // {
    //     return $this->cans(explode('|', $permission), true);
    // }

    public function emptyCache($type=null)
    {
        self::emptyIdCache($this->id, $type);
    }

    public static function emptyIdCache($user_id, $type=null)
    {
        //Log::debug("empty_cache user $user_id");

        Cache::forget('user-'.$user_id.'-is-admin');
        Cache::forget('user-'.$user_id.'-permissions');

        if ($type == null || $type == 'device')
        {
            Cache::forget('user-'.$user_id.'-all-device-ids');
            Cache::forget('user-'.$user_id.'-all-device-ids-editable');
        }
        // Cache::forget('user-'.$user_id.'-historic-device-ids');
        // Cache::forget('user-'.$user_id.'-all-device-objects');
        // Cache::forget('user-'.$user_id.'-all-active-device-objects');
        // Cache::forget('user-'.$user_id.'-all-devices-with-rentals-and-researches');
        // Cache::forget('user-'.$user_id.'-all-location-research-daysago');
        // Cache::forget('user-'.$user_id.'-all-location-research-ids');
        // Cache::forget('user-'.$user_id.'-all-location-area-ids');

        if ($type == null || $type == 'inspection')
        {
            Cache::forget('user-'.$user_id.'-all-inspection-ids');
            Cache::forget('user-'.$user_id.'-all-editable-inspection-ids');
            Cache::forget('user-'.$user_id.'-apiaries'); // clear cached apiaries, for last inspection on hive
        }

        if ($type == null || $type == 'apiary')
        {
            Cache::forget('user-'.$user_id.'-all-location-ids');
            Cache::forget('user-'.$user_id.'-all-my-location-ids');
            Cache::forget('user-'.$user_id.'-all-hive-ids');
            Cache::forget('user-'.$user_id.'-all-hive-ids-editable');
            Cache::forget('user-'.$user_id.'-apiaries');
            Cache::forget('locations-user-'.$user_id);
        }

        if ($type == null || $type == 'group')
        {
            Cache::forget('user-'.$user_id.'-group-hive-ids');
            Cache::forget('user-'.$user_id.'-group-hive-ids-editable');
            Cache::forget('user-'.$user_id.'-groups-and-invites');
        }

        Log::debug("User ID $user_id $type cache emptied");
    }

    public function getAvatarAttribute()
    {
        return !empty($this->attributes['avatar']) && substr($this->attributes['avatar'], 0, 8) == 'https://' ? $this->attributes['avatar'] : env('AWS_URL').'avatars/default.jpg';
    }

    public function getGlobalRateLimitPerMinAttribute()
    {
        return env('API_GLOBAL_RATE_LIMIT', 60); // rate for all app calls together
    }
    public function getGlobalRateLimitPerMinSensorsAttribute()
    {
        return !empty($this->attributes['rate_limit_per_min']) ? $this->attributes['rate_limit_per_min'] : env('API_GLOBAL_RATE_LIMIT_SENSOR_DATA', 10); // rate limit for only authenticated sensor data post
    }

    public function getAppDebugAttribute()
    {
        return $this->can('app-debug');
    }

    public function getAdminAttribute()
    {
        return Cache::remember('user-'.$this->id.'-is-admin', env('CACHE_TIMEOUT_LONG'), function () {
            return $this->hasRole(['superadmin', 'admin']);
        });
    }

    public function getPermissionsAttribute()
    {
        return Cache::remember('user-'.$this->id.'-permissions', env('CACHE_TIMEOUT_LONG'), function () {
            $permissions = null;
            $specialRoles = $this->roles()->whereNotIn('name', ['superadmin', 'admin']);
            if ($specialRoles->count() > 0) {
                $permissions = [];
                foreach ($specialRoles->get() as $r) {
                    $permissions = array_merge($permissions, $r->permissions()->pluck('name')->toArray());
                }
            }

            return $permissions;
        });
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

    public function groupHiveIds($editable = false)
    {
        $cache_name = 'user-'.$this->id.'-group-hive-ids'.($editable ? '-editable' : '');
        $hive_ids   = Cache::remember($cache_name, env('CACHE_TIMEOUT_LONG'), function () use ($editable){
            
            $group_ids = $this->groups->pluck('id')->toArray();
            $hive_ids  = [];
            if ($editable)
                $hive_ids  = DB::table('group_hive')->where('edit_hive', 1)->whereIn('group_id',$group_ids)->distinct('hive_id')->pluck('hive_id')->toArray();
            else
                $hive_ids  = DB::table('group_hive')->whereIn('group_id',$group_ids)->distinct('hive_id')->pluck('hive_id')->toArray();
            //die(print_r(['group_ids'=>$group_ids,'hive_ids'=>$hive_ids]));
            return $hive_ids;
        });
        return $hive_ids;
    }

    public function groupHives($editable = false)
    {
        return Hive::whereIn('id',$this->groupHiveIds($editable));
    }

    public function allHives($editable = false) // Including Group hives
    {
        $cache_name = 'user-'.$this->id.'-all-hive-ids'.($editable ? '-editable' : '');
        $all_ids = Cache::remember($cache_name, env('CACHE_TIMEOUT_LONG'), function () use ($editable) {
            $own_ids = $this->hives()->pluck('id');
            $hiv_ids = $this->groupHiveIds($editable);
            $all_ids = $own_ids->merge($hiv_ids);
            return $all_ids;
        });
        return Hive::whereIn('id',$all_ids);
    }


    public function hive_tags()
    {
        return $this->hasMany(HiveTag::class);
    }

    public function checklists()
    {
        return $this->belongsToMany(Checklist::class, 'checklist_user');
    }

    public function inspections()
    {
        return $this->belongsToMany(Inspection::class, 'inspection_user');
    }

    public function allInspections($editable = false) // Including Group hive locations
    {
        $cache_name = 'user-'.$this->id.'-all'.($editable?'-editable':'').'-inspection-ids';
        $all_ids = Cache::remember($cache_name, env('CACHE_TIMEOUT_LONG'), function () use ($editable){
            $own_ids = $this->inspections()->pluck('id');
            $hiv_ids = $this->groupHiveIds($editable);
            $ins_ids = DB::table('inspection_hive')->whereIn('hive_id',$hiv_ids)->distinct('inspection_id')->pluck('inspection_id')->toArray();
            $all_ids = $own_ids->merge($ins_ids);
            return $all_ids;
        });
        return Inspection::whereIn('id',$all_ids);
    }

    public function researches()
    {
        return $this->belongsToMany(Research::class, 'research_user');
    }
    
    public function researchesOwned()
    {
        return $this->hasMany(Research::class);
    }

    public function flashlogs()
    {
        return $this->hasMany(FlashLog::class);
    }

    public function allFlashlogs($editable = true) // Including (only) editable group hive flashlogs
    {
        $own_ids = $this->flashlogs()->pluck('id');
        $hiv_ids = $this->groupHives($editable)->pluck('id');
        $fla_ids = DB::table('flash_logs')->whereIn('hive_id',$hiv_ids)->pluck('id');
        $all_ids = $own_ids->merge($fla_ids);
        return FlashLog::whereIn('id',$all_ids);
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

    public function allDevices($editable = false) // Including Group hive locations
    {
        $cache_name = 'user-'.$this->id.'-all-device-ids'.($editable ? '-editable' : '');
        $all_ids    = Cache::remember($cache_name, env('CACHE_TIMEOUT_LONG'), function () use ($editable) {
            $own_ids = $this->devices()->pluck('id');
            $hiv_ids = $this->groupHiveIds($editable);
            $sen_ids = DB::table('sensors')->whereIn('hive_id',$hiv_ids)->distinct('hive_id')->pluck('id')->toArray();
            $all_ids = $own_ids->merge($sen_ids);
            return $all_ids;
        });
        return Device::whereIn('id',$all_ids);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_user')->whereNotNull('accepted');
    }

    public function dashboardGroups()
    {
        return $this->hasMany(DashboardGroup::class);
    }

    public function checklistSvgs()
    {
        return $this->hasMany(ChecklistSvg::class);
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


    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function all_location_ids($mine = false) // Including rental locations
    {
        $cache_name = 'user-'.$this->id.'-all'.($mine?'-my':'').'-location-ids';
        return Cache::remember($cache_name, env('CACHE_TIMEOUT_LONG'), function () use ($mine){
            $own_ids = $this->locations()->pluck('id');
            $hiv_ids = $this->groupHives($mine)->pluck('id');
            $loc_ids = DB::table('hives')->whereIn('id',$hiv_ids)->distinct('hive_id')->pluck('location_id')->toArray();
            $all_ids = $own_ids->merge($loc_ids);
            return $all_ids;
        });
    }

    public function allLocations($mine = false) // Including rental locations
    {
        $all_ids = $this->all_location_ids($mine);
        return Location::whereIn('id', $all_ids);
    }

    public function allApiaries(){
        return Cache::remember('user-'.$this->id.'-apiaries', env('CACHE_TIMEOUT_LONG'), function () {
            return $this->locations()->with(['hives.layers', 'hives.queen'])->get();
        });
    }

    public function groupInvitations()
    {
        $user_id = $this->id;
        return $this->belongsToMany(Group::class, 'group_user')->whereNotNull('invited')->whereNull('accepted')->whereNull('declined')->get()->map(function ($item, $key) use ($user_id)
        {
            $invite              = $item->only(['id','name','description','color']);
            $groupUserArray      = $item->users->firstWhere('id',$user_id);
            $invite['invited']   = (isset($groupUserArray['invited'])) ? $groupUserArray['invited'] : null;
            $invite['token']     = (isset($groupUserArray['token'])) && $user_id == Auth::user()->id ? $groupUserArray['token'] : null; // only if yourself, add tokens to accept group invites
            $invite['hivecount'] = $item->hives->count();
            $invite['usercount'] = $item->users->whereNotNull('accepted')->count();
            return $invite; 
        });
    }

    public function groupsAndInvites()
    {
        return Cache::remember('user-'.$this->id.'-groups-and-invites', env('CACHE_TIMEOUT_LONG'), function () {
            $groups = $this->groups()->orderBy('name')->get();
            $invite = $this->groupInvitations();
            $date   = date('Y-m-d H:i:s');
            return ['invitations'=>$invite, 'groups'=>$groups, 'cache_date'=>$date];
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
        // delete all related items 
        $this->settings()->delete();
        $this->images()->delete();
        $this->devices()->delete();
        $this->locations()->delete(); //including $cascadeDeletes = ['hives', 'inspections']; // including $cascadeDeletes = ['queen','inspections','layers','frames','productions'];
        $this->checklists()->delete();
        $this->alert_rules()->delete();
        $this->alerts()->delete();

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

    public static function selectList($index='id')
    {
        if (Auth::user()->hasRole('superadmin'))
        {
            $users = User::orderBy('name')->get();
            $array = [];
            foreach ($users as $u) 
            {
                if ($u->name != $u->email)
                    $array[$u[$index]] = $u->name.' ('.$u->email.')';
                else
                    $array[$u[$index]] = $u->email;
            }
            return $array;
        }
        
        return Auth::user()->pluck('name','id');
    }
}
