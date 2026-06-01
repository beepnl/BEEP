<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    protected $fillable = ['user_id', 'category_id', 'name', 'value', 'number'];

    protected $guarded = ['id'];

    protected $hidden = ['user_id', 'category_id', 'id', 'deleted_at'];
    // protected $appends  = ['type'];

    public $timestamps = false;

    // Relations
    public function getTypeAttribute()
    {
        return Category::find($this->category_id)->name;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
