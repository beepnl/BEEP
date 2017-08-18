<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
    protected $fillable = ['user_id', 'hive_id', 'category_id', 'name', 'key'];
	protected $guarded 	= ['id'];
    protected $hidden   = ['user_id', 'category_id'];
    protected $appends  = ['type'];

    public $timestamps = false;

    // Relations
    public function getTypeAttribute()
    {
        return Category::find($this->category_id)->name;
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
