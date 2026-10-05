<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('dashboard_groups', 'id')]
#[Fillable('user_id', 'code', 'name', 'hive_ids', 'speed', 'interval', 'show_inspections', 'show_all', 'hide_measurements', 'logo_url', 'description')]
#[Hidden('user_id', 'user', 'created_at', 'updated_at')]
class DashboardGroup extends Model
{
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
