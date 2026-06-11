<?php

namespace App;

use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[WithoutTimestamps]
#[Fillable('layer_id', 'category_id', 'present', 'order')]
#[Guarded('id')]
#[Hidden('category_id', 'layer_id', 'created_at', 'deleted_at')]
#[Appends('type')]
class HiveLayerFrame extends Model
{
    use HasFactory;
    use SoftDeletes;

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
