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

    // only brood and honey layers can have frames since 2023-06-14, so remove all other frames once
    public static function deleteNonBroodAndHoneyFrames()
    {
        $brood_and_honey_cats = [];
        $brood_and_honey_cats[] = Category::findCategoryIdByParentAndName('hive_layer', 'brood');
        $brood_and_honey_cats[] = Category::findCategoryIdByParentAndName('hive_layer', 'honey');

        $layers = HiveLayer::whereNotIn('category_id', $brood_and_honey_cats)->get();
        echo("Processing ".$layers->count()." non brood-and-honey layers, not in: ".implode(', ',$brood_and_honey_cats)."\n");
        foreach($layers as $layer)
        {
            $type = $layer->type;
            if ($type != 'brood' && $type != 'honey' && $layer->framecount > 0)
            {
                echo("Layer $layer->id type $type removing $layer->framecount frames\n");
                $layer->frames()->delete();
            }
        }
        echo("Finished processing ".$layers->count()." non brood-and-honey layers, not in: ".implode(', ',$brood_and_honey_cats)."\n");
    }
}
