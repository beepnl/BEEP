<?php

namespace App;

use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Auth;

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
    	return $hives = $this->hives()->with(['layers', 'queen'])->withPivot('edit_hive')->get()->map(function ($item, $key)
        {
            $editable = $item->pivot->edit_hive;
            unset($item->pivot);
            $item->editable = $editable;
            return $item; 
        });
    }

    public function getHiveIdsAttribute()
    {
    	return $this->hives()->pluck('editable','id');
    }

    public function getUsersAttribute()
    {
        return $this->users()->withPivot('admin', 'creator')->get()->map(function ($item, $key)
        {
            $user          = $item->only(['id','name','avatar','email']);
            $user['admin']  = $item->pivot->admin;
            $user['creator']= $item->pivot->creator;
            return $user; 
        });
    }

    public function getAdminAttribute()
    {
        return $this->users()->where('id',Auth::user()->id)->where('admin',1)->count();
    }

    public function getCreatorAttribute()
    {
        return $this->users()->where('id',Auth::user()->id)->where('creator',1)->count();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'group_user');
    }

    public function hives()
    {
        return $this->belongsToMany(Hive::class, 'group_hive');
    }

}
