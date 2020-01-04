<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use InterventionImage;
use Storage;

class Image extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'images';

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
    protected $fillable = ['file', 'description', 'type', 'height', 'width', 'size_kb', 'date', 'user_id', 'category_id', 'checklist_id'];



    public function category()
    {
        return $this->hasOne('Category::class');
    }
    public function user()
    {
        return $this->hasOne('User::class');
    }
    public function checklist()
    {
        return $this->hasOne('Checklist::class');
    }

    public static $storage      = 's3';
    public static $thumbPixels  = 200;
    public static $thumbQuality = 70;
    public static $photoPath    = 'photos/';
    public static $thumbPath    = 'thumbs/';

    public static function storePhoto($requestData, $type='camera')
    {
        //get file extension
        $imageFile = $requestData['file'];
        $extension = $imageFile->getClientOriginalExtension();

        //filename to store
        $pathname  = $type; // the piece in between [photo/thumb path] and [fileName]
        
        $filePath  = Storage::disk($storage)->putFile(InterventionImage::$photoPath.$pathname, $imageFile);
        
        // Store thumbnail
        if ($filePath)
        {
            $fileName      = substr($filePath, strlen(InterventionImage::$photoPath) + strlen($pathname) + 1); // only file name
            $filePath      = Storage::disk($storage)->putFileAs(InterventionImage::$thumbPath.$pathname, $imageFile, $fileName);
            $photoFilePath = Storage::disk($storage)->url.$filePath;
            InterventionInterventionImage::make($photoFilePath)->resize(InterventionImage::$thumbPixels, InterventionImage::$thumbPixels, function($constraint) { $constraint->aspectRatio(); })->save($photoFilePath, InterventionImage::$thumbQuality);
        
            return $fileName;
        }
        return null;
    }

    public function delete()
    {
        // delete all related photos 
        $pathPhoto = InterventionImage::$photoPath.InterventionImage::$sensorPath.$this->sensor_id.'/'.$this->type.'/'.$this->file;
        if (Storage::disk($storage)->exists($pathPhoto));
            Storage::disk($storage)->delete($pathPhoto);

        $pathThumb = InterventionImage::$thumbPath.InterventionImage::$sensorPath.$this->sensor_id.'/'.$this->type.'/'.$this->file;
        if (Storage::disk($storage)->exists($pathThumb));
            Storage::disk($storage)->delete($pathThumb);

        // delete the photo
        return parent::delete();
    }
    
}
