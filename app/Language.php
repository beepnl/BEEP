<?php

namespace App;

use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[WithoutTimestamps]
#[Fillable('name', 'name_english', 'icon', 'abbreviation', 'twochar')]
#[Appends('lang')]
class Language extends Model
{
    public function getLangAttribute()
    {
        return $this->twochar;
    }

    // Relations
    public function translations(): BelongsToMany
    {
        return $this->belongsToMany(Translation::class);
    }
}
