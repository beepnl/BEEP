<?php

namespace App;

use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use DB;
use Auth;
use App\Hive;

class Group extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    public $timestamps = false;

    public $fillable = ['type','name','description','hex_color','icon'];

    protected $hidden= ['pivot', 'deleted_at', 'type', 'icon'];

    public $appends  = ['hives','users','admin','creator']; // 'hive_ids'

    // Relations
    public function getHivesAttribute()
    {
    	return $this->hives()->with(['layers', 'queen'])->get();
    }

    public function getHiveIdsAttribute()
    {
    	$hive_ids = DB::table('group_hive')->where('group_id',$this->id)->pluck('hive_id')->toArray();
        return $hive_ids; //Hive::whereIn('id',$hive_ids)->pluck('id');
    }

    public function getUsersAttribute()
    {
        return $this->users()->withPivot('admin', 'creator', 'invited', 'accepted', 'declined', 'token')->get()->map(function ($item, $key)
        {
            $user            = $item->only(['id','name','avatar','email']);
            $user['admin']   = (bool)$item->pivot->admin;
            $user['creator'] = (bool)$item->pivot->creator;
            $user['invited'] = $item->pivot->invited;
            $user['accepted']= $item->pivot->accepted;
            $user['declined']= $item->pivot->declined;
            $user['token']   = ($user['id'] == Auth::user()->id) ? $item->pivot->token : null; // only if yourself, add tokens to accept group invites

            return $user; 
        });
    }

    public function getAdminAttribute()
    {
        return (bool)($this->users()->where('id',Auth::user()->id)->where('admin',1)->count() > 0); // myself
    }

    public function getCreatorAttribute()
    {
        return (bool)($this->users()->where('id',Auth::user()->id)->where('creator',1)->count() > 0); // myself
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'group_user');
    }

    public function hives()
    {
        return $this->belongsToMany(Hive::class, 'group_hive');
    }

    public function delete()
    {
        // delete all related items 
        $this->hives()->detach();
        $this->users()->detach();

        // delete the user
        return parent::delete();
    }

}
