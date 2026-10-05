<?php

namespace App;

use Cache;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

#[WithoutTimestamps]
#[Fillable('hive_id', 'category_id', 'order', 'color')]
#[Guarded('id')]
#[Hidden('category_id', 'hive_id', 'created_at', 'deleted_at', 'frames')]
#[Appends('type', 'framecount')]
class HiveLayer extends Model
{
    use HasFactory;
    use SoftDeletes;

    // Cache functions
    public static function boot()
    {
        parent::boot();

        static::created(function ($l) {
            $l->empty_cache();
        });

        static::updated(function ($l) {
            $l->empty_cache();
        });

        static::deleted(function ($l) {
            $l->empty_cache();
        });
    }

    public function empty_cache($clear_hive = true)
    {
        Log::debug("Hive layer ID $this->id cache emptied");

        if ($clear_hive) {
            $this->hive->empty_cache();
        }
    }

    // Relations
    public function getFramecountAttribute()
    {
        return $this->frames()->count();
    }

    public function getTypeAttribute()
    {
        return Cache::rememberForever("hive-layer-type-$this->category_id-name", function () {
            return Category::find($this->category_id)->name;
        });
    }

    public function hive(): BelongsTo
    {
        return $this->belongsTo(Hive::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function frames(): HasMany
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
        echo 'Processing '.$layers->count().' non brood-and-honey layers, not in: '.implode(', ', $brood_and_honey_cats)."\n";
        foreach ($layers as $layer) {
            $type = $layer->type;
            if ($type != 'brood' && $type != 'honey' && $layer->framecount > 0) {
                echo "Layer $layer->id type $type removing $layer->framecount frames\n";
                $layer->frames()->delete();
            }
        }
        echo 'Finished processing '.$layers->count().' non brood-and-honey layers, not in: '.implode(', ', $brood_and_honey_cats)."\n";
    }
}
