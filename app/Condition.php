<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Condition extends Model
{
    protected $fillable = ['hive_id', 'category_id', 'text', 'number', 'score', 'boolean'];
	protected $guarded 	= ['id'];
	protected $hidden   = ['deleted_date'];
    protected $appends  = ['name', 'type'];

    public $timestamps = false;

    // Relations
    public function getTypeAttribute()
    {
        return Category::find($this->category_id)->type;
    }

    public function getNameAttribute()
    {
        return Category::find($this->category_id)->name;
    }
    
	public function hive()
    {
        return $this->belongsTo(Hive::class);
    }

    public function type()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function typeName()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

}
