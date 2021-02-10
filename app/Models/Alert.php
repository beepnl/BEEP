<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AlertRule;
use App\Measurement;
use App\Location;
use App\Hive;
use App\Device;
use App\User;

class Alert extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'alerts';

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
    protected $fillable = ['alert_rule_id', 'alert_function', 'alert_value', 'measurement_id', 'show', 'location_name', 'hive_name', 'device_name', 'location_id', 'hive_id', 'device_id', 'user_id'];

    public function alert_rule()
    {
        return $this->belongsTo(AlertRule::class);
    }
    public function measurement()
    {
        return $this->belongsTo(Measurement::class);
    }
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
    public function hive()
    {
        return $this->belongsTo(Hive::class);
    }
    public function device()
    {
        return $this->belongsTo(Device::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
}
