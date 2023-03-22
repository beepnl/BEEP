<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\User;

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
    protected $fillable = ['user_id', 'code', 'name', 'hive_ids', 'speed', 'interval', 'show_inspections', 'show_all', 'hide_measurements', 'logo_url'];

    protected $casts = ['hive_ids'=>'array'];

    public static $intervals = ['hour', 'day', 'week', 'month', 'year', 'selection'];

    public function hives()
    {
        return $this->user->allHives()->whereIn('id', $this->hive_ids);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
}
