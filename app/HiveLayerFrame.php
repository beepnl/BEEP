<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HiveLayerFrame extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['layer_id', 'category_id', 'present', 'order'];

    protected $guarded = ['id'];

    protected $hidden = ['category_id', 'layer_id', 'created_at', 'deleted_at'];

    protected $appends = ['type'];

    public $timestamps = false;

    // Relations
    public function getTypeAttribute()
    {
        return Category::find($this->category_id)->name;
    }

    public function layer(): BelongsTo
    {
        return $this->belongsTo(HiveLayer::class, 'layer_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
