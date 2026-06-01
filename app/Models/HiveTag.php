<?php

namespace App\Models;

use App\Hive;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HiveTag extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'hive_tags';

    /**
     * The database primary key value.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'tag', 'hive_id', 'action_id', 'router_link'];

    protected $hidden = ['id', 'created_at', 'updated_at', 'user_id'];

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
