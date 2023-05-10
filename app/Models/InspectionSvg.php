<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Checklist;
use App\User;

class InspectionSvg extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'inspection_svgs';

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
    protected $fillable = ['user_id', 'checklist_id', 'name', 'svg', 'pages', 'last_print'];

    public function checklist()
    {
        return $this->belongsTo(Checklist::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
}
