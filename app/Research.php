<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Picture;
use Auth;

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
    protected $fillable = ['description', 'name', 'url', 'image', 'type', 'institution', 'type_of_data_used', 'start_date', 'end_date'];
    protected $hidden   = ['users', 'deleted_at'];
    protected $appends  = ['consent', 'checklists_names'];

    public static $pictureType = 'research';

    public static function storeImage($requestData)
    {
        return Picture::store($requestData, Research::$pictureType);
    }

    public function getConsentAttribute()
    {
        return $this->users->contains(Auth::user()) ? true : false;
    }

    public function getChecklistsNamesAttribute()
    {
        return $this->checklists()->pluck('name');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'research_user');
    }

    public function checklists()
    {
        return $this->belongsToMany(Checklist::class, 'checklist_research');
    }
    
    public function delete()
    {
        // delete all related photos 
        Picture::remove(Research::$pictureType.'/'.$this->image);

        // delete the photo
        return parent::delete();
    }

}
