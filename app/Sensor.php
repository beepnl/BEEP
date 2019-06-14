<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Sensor extends Model
{
    protected $fillable = ['user_id', 'hive_id', 'category_id', 'name', 'key'];
	protected $guarded 	= ['id'];
    protected $hidden   = ['user_id', 'category_id', 'deleted_at'];
    protected $appends  = ['type','hive_name', 'location_name', 'owner'];

    public $timestamps = false;

    // Relations
    public function getTypeAttribute()
    {
        return Category::find($this->category_id)->name;
    }

    public function getHiveNameAttribute()
    {
        if (isset($this->hive))
            return $this->hive->name;

        return '';
    }

    public function getLocationNameAttribute()
    {
        if (isset($this->hive))
            return $this->hive->getLocationAttribute();

        return '';
    }

    public function getOwnerAttribute()
    {
        if ($this->user_id == Auth::user()->id)
            return true;
        
        return false;
    }
    
	public function hive()
    {
        return $this->belongsTo(Hive::class);
    }

	public function user()
    {
        return $this->belongsTo(User::class);
    }
}
