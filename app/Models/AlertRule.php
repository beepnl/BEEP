<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Measurement;
use App\User;

class AlertRule extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'alert_rules';

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
    protected $fillable = ['name', 'description', 'measurement_id', 'calculation', 'calculation_minutes', 'comparator', 'comparison', 'threshold_value', 'exclude_months', 'exclude_hours', 'alert_via_email', 'webhook_url', 'active', 'user_id', 'default_rule'];

    public function measurement()
    {
        return $this->belongsTo(Measurement::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public static function selectList()
    {
        return AlertRule::orderBy('name')->pluck('name','id');
    }
}
