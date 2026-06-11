<?php

namespace App\Models;

use App\Hive;
use App\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('hive_tags', 'id')]
#[Fillable('user_id', 'tag', 'hive_id', 'action_id', 'router_link')]
#[Hidden('id', 'created_at', 'updated_at', 'user_id')]
class HiveTag extends Model
{
    protected function casts(): array
    {
        return [
            'router_link' => 'array',
        ];
    }

    public function hive(): BelongsTo
    {
        return $this->belongsTo(Hive::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
