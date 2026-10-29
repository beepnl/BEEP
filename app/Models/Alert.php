<?php

namespace App\Models;

use App\Device;
use App\Hive;
use App\Location;
use App\Measurement;
use App\User;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('alerts', 'id')]
#[Fillable('alert_rule_id', 'alert_function', 'alert_value', 'measurement_id', 'show', 'location_name', 'hive_name', 'device_name', 'location_id', 'hive_id', 'device_id', 'user_id', 'count')]
#[Hidden('show', 'alert_rule')]
#[Appends('alert_rule_name')]
class Alert extends Model
{
    // Relations
    public function getAlertRuleNameAttribute()
    {
        if ($this->alert_rule_id != null && $this->alert_rule) {
            return $this->alert_rule->name;
        }

        return null;
    }

    public function alert_rule(): BelongsTo
    {
        return $this->belongsTo(AlertRule::class);
    }

    public function measurement(): BelongsTo
    {
        return $this->belongsTo(Measurement::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function hive(): BelongsTo
    {
        return $this->belongsTo(Hive::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
