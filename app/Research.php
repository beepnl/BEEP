<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Image;
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
    protected $fillable = ['description', 'name', 'url', 'image_id', 'type', 'institution', 'type_of_data_used', 'start_date', 'end_date'];
    protected $hidden   = ['users', 'deleted_at'];
    protected $appends  = ['consent', 'checklist_names', 'image'];

    public static $pictureType = 'research';

    public static function storeImage($requestData)
    {
        return Image::store($requestData, Research::$pictureType);
    }

    public function getConsentAttribute()
    {
        return $this->users->contains(Auth::user()) ? true : false;
    }

    public function getChecklistNamesAttribute()
    {
        return $this->checklists()->pluck('name');
    }

    public function getImageAttribute()
    {
        if (isset($this->image_id))
            return $this->image()->thumb_url;

        return null;
    }
    
    public function users()
    {
        return $this->belongsToMany(User::class, 'research_user');
    }

    public function checklists()
    {
        return $this->belongsToMany(Checklist::class, 'checklist_research');
    }

    public function image()
    {
        return $this->hasOne(Image::class);
    }
    
    public function delete()
    {
        // delete image 
        if(isset($this->image_id))
            $this->image()->delete();

        // delete the research
        return parent::delete();
    }

}
