<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Checklist;
use App\User;
use Illuminate\Database\Eloquent\Model;

class ChecklistSvg extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'checklist_svgs';

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
    protected $fillable = ['user_id', 'checklist_id', 'name', 'svg', 'pages', 'last_print', 'app_version'];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
