<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Hive;

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
    protected $hidden   = ['id', 'created_at', 'updated_at', 'user_id'];
    protected $casts    = ['router_link'=>'array'];

    public function hive()
    {
        return $this->belongsTo(Hive::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
}
