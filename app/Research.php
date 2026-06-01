<?php

namespace App;

use Auth;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Research extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'researches';

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
    protected $fillable = ['description', 'name', 'url', 'image_id', 'type', 'institution', 'type_of_data_used', 'start_date', 'end_date', 'user_id', 'default_user_ids', 'visible', 'on_invite_only'];

    protected $hidden = ['users', 'deleted_at', 'user_id', 'owner', 'viewers', 'visible'];

    protected $appends = ['consent', 'consent_history', 'checklist_names', 'thumb_url'];

    protected $casts = [
        'default_user_ids' => 'array',
    ];

    public static $pictureType = 'research';

    public static function storeImage($requestData)
    {
        return Image::store($requestData, Research::$pictureType);
    }

    public function getConsentAttribute()
    {
        $consent = DB::table('research_user')->where('research_id', $this->id)->where('user_id', Auth::user()->id)->orderBy('updated_at', 'desc')->limit(1)->value('consent');

        if ($consent === 1) {
            return true;
        }

        return false;
    }

    public function getConsentHistoryAttribute()
    {
        return DB::table('research_user')->where('research_id', $this->id)->where('user_id', Auth::user()->id)->orderBy('updated_at', 'desc')->get();
    }

    public function getChecklistNamesAttribute()
    {
        return $this->checklists()->pluck('name');
    }

    public function getThumbUrlAttribute()
    {
        if (isset($this->image_id)) {
            return isset($this->image->thumb_url) ? $this->image->thumb_url : null;
        }

        return null;
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'research_user')->distinct('user_id');
    }

    public function viewers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'research_viewer');
    }

    public function checklists(): BelongsToMany
    {
        return $this->belongsToMany(Checklist::class, 'checklist_research');
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }

    public function delete()
    {
        // delete image
        if (isset($this->image_id)) {
            $this->image()->delete();
        }

        // delete the research
        return parent::delete();
    }
}
