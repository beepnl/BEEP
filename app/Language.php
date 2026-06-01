<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = ['name', 'name_english', 'icon', 'abbreviation', 'twochar'];

    protected $appends = ['lang'];

    public $timestamps = false;

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
