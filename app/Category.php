<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'type', 'options', 'parent_id'];
	protected $guarded 	= ['id'];
	protected $hidden   = ['created_at','updated_at','parent_id'];
    //protected $appends  = ['parent', 'parent_type'];

    // Relations
    public function getParentAttribute()
    {
        $parent = Category::find($this->parent_id);
        return $parent ? $parent->name : null;
    }

    public function getParentTypeAttribute()
    {
        $parent = Category::find($this->parent_id);
        return $parent ? $parent->type : null;
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'id', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id', 'id');
    }
    
    // finding on ::type()
    public function scopeType($query, $type) 
    {
        return $query->where('type', $type);
    }
    // finding on ::name()
    public function scopeName($query, $name) 
    {
        return $query->where('name', $name);
    }
    // finding on ::name()
    public function scopeTypeName($query, $type, $name) 
    {
        return $query->where('type', $type)->where('name', $name);
    }
    // finding on ::child()
    public static function findCategoryByParentAndName($parent_name, $name) 
    {
        $parent = Category::where('name', $parent_name)->first();
        if (isset($parent))
            return $parent->children()->where('name', $name)->first();

        return null;
    }

    public static function findCategoryIdByParentAndName($parent_name, $name)
    {
        $cat = Category::findCategoryByParentAndName($parent_name, $name);
        if (isset($cat))
            return $cat->id;

        return null;
    }


}
