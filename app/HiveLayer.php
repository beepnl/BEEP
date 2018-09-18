<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HiveLayer extends Model
{
    use SoftDeletes;

    protected $fillable = ['hive_id', 'category_id', 'order', 'color'];
	protected $guarded 	= ['id',];
    protected $hidden   = ['category_id', 'hive_id', 'created_at', 'deleted_at', 'frames'];
    protected $appends  = ['type', 'framecount'];

    public $timestamps = false;

    // Relations
    public function getFramecountAttribute()
    {
        return $this->frames()->count();
    }

    public function getTypeAttribute()
    {
        return Category::find($this->category_id)->name;
    }

	public function hive()
    {
        return $this->belongsTo(Hive::class);
    }

    public function type()
    {
        return $this->belongsTo(Category::class);
    }

    public function frames()
    {
        return $this->hasMany(HiveLayerFrame::class, 'layer_id');
    }
}
