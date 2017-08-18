<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hive extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'location_id', 'hive_type_id', 'color', 'name'];
    protected $guarded  = ['id'];
	protected $hidden 	= ['user_id'];
    protected $appends  = ['type','location','attention','impression','note'];

    public $timestamps = false;

	// Relations
	public function getTypeAttribute()
    {
        return HiveType::find($this->hive_type_id)->name;
    }

    public function getLocationAttribute()
    {
        $loc = Location::find($this->location_id);
        return isset($loc) ? $loc->name : '';
    }

    public function getAttentionAttribute()
    {
        $attention_id = Category::findCategoryIdByParentAndName('overall','needs_attention');
        $condition    = $this->conditions()->where('category_id', $attention_id)->orderBy('created_at', 'desc')->limit(1)->pluck('boolean')->toArray();
        return isset($condition) && count($condition) > 0 ? $condition[0] : null;
    }

    public function getImpressionAttribute()
    {
        $impression_id= Category::findCategoryIdByParentAndName('overall','positive_impression');
        $condition    = $this->conditions()->where('category_id', $impression_id)->orderBy('created_at', 'desc')->limit(1)->pluck('score')->toArray();
        return isset($condition) && count($condition) > 0 ? $condition[0] : null;
    }

    public function getNoteAttribute()
    {
        $note_id      = Category::findCategoryIdByParentAndName('overall','notes');
        $condition    = $this->conditions()->where('category_id', $note_id)->orderBy('created_at', 'desc')->limit(1)->pluck('text')->toArray();
        return isset($condition) && count($condition) > 0 ? $condition[0] : null;
    }

    public function getHoneylayersAttribute()
    {
        return $this->layers()->where('category_id', Category::findCategoryIdByParentAndName('hive_layer','honey'))->count();
    }

    public function getBroodlayersAttribute()
    {
        return $this->layers()->where('category_id', Category::findCategoryIdByParentAndName('hive_layer','brood'))->count();
    }

    public function queen()
    {
        return $this->hasOne(Queen::class);
    }

    public function type()
    {
        return $this->belongsTo(HiveType::class);
    }
	
	public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Hive buildup
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function layers()
    {
        return $this->hasMany(HiveLayer::class);
    }

    public function frames()
    {
        return $this->hasManyThrough(HiveLayerFrame::class, HiveLayer::class, 'hive_id', 'layer_id');
    }
    
    // manually inserted items
    public function conditions()
    {
        return $this->hasMany(Condition::class);
    }

    public function actions()
    {
        return $this->hasMany(Action::class);
    }

    public function productions()
    {
        return $this->hasMany(Production::class);
    }
}
