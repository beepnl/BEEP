<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardGroup extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'dashboard_groups';

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
    protected $fillable = ['user_id', 'code', 'name', 'hive_ids', 'speed', 'interval', 'show_inspections', 'show_all', 'hide_measurements', 'logo_url', 'description'];

    protected $hidden = ['user_id', 'user', 'created_at', 'updated_at'];

    public static $intervals = ['hour' => 'Hour', 'day' => 'Day', 'week' => 'Week', 'month' => 'Month', 'year' => 'Year', 'selection' => 'Selection'];

    protected function casts(): array
    {
        return [
            'hive_ids' => 'array',
        ];
    }

    public function hives()
    {
        if (is_array($this->hive_ids) && count($this->hive_ids) > 0) {
            return $this->user->hives()->whereIn('id', $this->hive_ids);
        }

        return collect();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
