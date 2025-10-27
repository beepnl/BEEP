<?php

namespace App;

use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

use DB;
use Auth;
use Cache;
use App\Hive;

class Group extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    public $timestamps = false;

    public $fillable = ['type','name','description','hex_color','icon'];

    protected $hidden= ['pivot', 'deleted_at', 'type', 'icon'];

    public $appends  = ['hives','users','admin','creator']; // 'hive_ids'

    // Caching
    public static function boot()
    {
        parent::boot();

        static::created(function ($g) {
            $g->empty_cache();
        });

        static::updated(function ($g) {
            $g->empty_cache();
        });

        static::deleted(function ($g) {
            $g->empty_cache();
        });
    }

    // Cache functions
    public function empty_cache($clear_users=true)
    {
        Cache::forget('group-'.$this->id.'-hives');

        Log::debug("Group ID $this->id cache emptied");

        if ($clear_users)
        {
            foreach ($this->group_users as $user) {
                $user->emptyCache('group');
            }
            // clear group users cache after clearing users cache 
            Cache::forget('group-'.$this->id.'-users');
        }
    }



    // Relations
    public function getHivesAttribute()
    {
    	return Cache::remember('group-'.$this->id.'-hives', env('CACHE_TIMEOUT_LONG'), function () {
            return $this->group_hives()->with(['layers', 'queen'])->get();
        });
    }

    public function getHiveIdsAttribute()
    {
    	$hive_ids = DB::table('group_hive')->where('group_id',$this->id)->pluck('hive_id')->toArray();
        return $hive_ids; //Hive::whereIn('id',$hive_ids)->pluck('id');
    }

    public function getUsersAttribute()
    {
        return Cache::remember('group-'.$this->id.'-users', env('CACHE_TIMEOUT_LONG'), function () {
            return $this->group_users()->withPivot('admin', 'creator', 'invited', 'accepted', 'declined', 'token')->get()->map(function ($item, $key)
            {
                $user            = $item->only(['id','name','avatar','email']);
                $user['admin']   = (bool)$item->pivot->admin;
                $user['creator'] = (bool)$item->pivot->creator;
                $user['invited'] = $item->pivot->invited;
                $user['accepted']= $item->pivot->accepted;
                $user['declined']= $item->pivot->declined;
                $user['token']   = $item->pivot->token; 
                return $user; 
            });
        });
    }

    public function getAdminAttribute()
    {
        return (bool)($this->getUsersAttribute()->where('id',Auth::user()->id)->where('admin',1)->count() > 0); // myself
    }

    public function getCreatorAttribute()
    {
        return (bool)($this->getUsersAttribute()->where('id',Auth::user()->id)->where('creator',1)->count() > 0); // myself
    }

    public function group_users()
    {
        return $this->belongsToMany(User::class, 'group_user');
    }

    public function group_hives()
    {
        return $this->belongsToMany(Hive::class, 'group_hive');
    }

    public function delete()
    {
        // delete all related items 
        $this->group_hives()->detach();
        $this->group_users()->detach();

        // delete the user
        return parent::delete();
    }

}
