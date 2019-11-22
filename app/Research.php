<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Picture;

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
    protected $fillable = ['description', 'name', 'image', 'type', 'institution', 'type_of_data_used', 'start_date', 'end_date', 'checklist_id'];
    
    public static $pictureType = 'research';

    public static function storeImage($requestData)
    {
        return Picture::store($requestData, Research::$pictureType);
    }

    public function checklist()
    {
        return $this->hasOne(Checklist::class);
    }
    
    public function delete()
    {
        // delete all related photos 
        Picture::remove(Research::$pictureType.'/'.$this->image);

        // delete the photo
        return parent::delete();
    }

}
