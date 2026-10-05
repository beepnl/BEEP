<?php

namespace App\Models;

use App\Checklist;
use App\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('checklist_svgs', 'id')]
#[Fillable('user_id', 'checklist_id', 'name', 'svg', 'pages', 'last_print', 'app_version')]
class ChecklistSvg extends Model
{
    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
