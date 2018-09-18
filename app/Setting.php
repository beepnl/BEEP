<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
	protected $fillable = ['user_id', 'category_id', 'name', 'value', 'number'];
	protected $guarded 	= ['id'];
	protected $hidden   = ['user_id', 'category_id', 'id', 'deleted_at'];
    //protected $appends  = ['type'];

    public $timestamps = false;

    // Relations
    public function getTypeAttribute()
    {
        return Category::find($this->category_id)->name;
    }
    
	public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function type()
    {
        return $this->belongsTo(Category::class);
    }
}
