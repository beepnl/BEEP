<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use InterventionImage;
use Storage;
use Auth;

class Image extends Model
{
    
    public static $storage      = 's3';
    public static $maxPizelSize = 2000;
    public static $thumbPixels  = 200;
    public static $thumbQuality = 70;
    public static $imageDir     = 'images/';
    public static $thumbDir     = 'thumbs/';

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
    protected $fillable = ['file', 'description', 'type', 'height', 'width', 'size_kb', 'date', 'user_id', 'hive_id', 'category_id', 'checklist_id'];

    protected $appends = ['thumb'];


    public function getThumbAttribute()
    {
        return $this->getImageThumb();
    }


    public function category()
    {
        return $this->hasOne('Category::class');
    }
    public function user()
    {
        return $this->hasOne('User::class');
    }
    public function hive()
    {
        return $this->hasOne('Hive::class');
    }
    public function checklist()
    {
        return $this->hasOne('Checklist::class');
    }

    public function getImage()
    {
        return Storage::disk(Image::$storage)->get(Image::getImagePath($this->file, $this->type));
    }

    public function getImageThumb()
    {
        return Storage::disk(Image::$storage)->get(Image::getImagePath($this->file, $this->type, true));
    }

    public function imagePath()
    {
        return Storage::disk(Image::$storage)->url(Image::getImagePath($this->file, $this->type));
    }

    public function imageThumbPath()
    {
        return Storage::disk(Image::$storage)->url(Image::getImagePath($this->file, $this->type, true));
    }

    public static function getImagePath($file, $type='inspection', $thumb=false)
    {
        $dir = $thumb ? Image::$thumbDir : Image::$imageDir;
        return $dir.$type.'/'.$file;
    }

    public static function store($requestData, $type='inspection')
    {
        $anu = function($constraint)
        { 
            $constraint->aspectRatio(); 
            $constraint->upsize(); 
        };

        //get file extension
        $imageFile = $requestData['file'];
        if ($imageFile->isValid())
        {
            //filename to store
            $extension = $imageFile->getClientOriginalExtension();
            $fileName  = str_random(60).'.'.$extension;
            $imagePath = Image::getImagePath($fileName, $type);
            $thumbPath = Image::getImagePath($fileName, $type, true);

            // save big image
            $image       = InterventionImage::make($imageFile)->resize(Image::$maxPizelSize, Image::$maxPizelSize, $anu);
            $imageHeight = $image->getHeight();
            $imageWidth  = $image->getWidth();
            $imageStored = Storage::disk(Image::$storage)->put($imagePath, $image->stream());
            $fileSize    = Storage::disk(Image::$storage)->size($imagePath);
            $fileTime    = Storage::disk(Image::$storage)->lastModified($imagePath);
            $fileTimeStamp = date('Y-m-d H:i:s', $fileTime);
            
            // save thumbnail
            $thumb       = InterventionImage::make($image)->resize(Image::$thumbPixels, Image::$thumbPixels, $anu);
            $thumbStored = Storage::disk(Image::$storage)->put($thumbPath, $thumb->stream());
            
            if ($imageStored)
            {
                $saveArr = [
                    'file'        => $fileName,
                    'description' => isset($requestData['description']) ? $requestData['description'] : null,
                    'type'        => $type,
                    'height'      => $imageHeight,
                    'width'       => $imageWidth,
                    'size_kb'     => round($fileSize/1024),
                    'date'        => $fileTimeStamp,
                    'user_id'     => Auth::user()->id,
                    'hive_id'     => isset($requestData['hive_id']) ? $requestData['hive_id'] : null,
                    'category_id' => isset($requestData['category_id']) ? $requestData['category_id'] : null,
                    'checklist_id'=> isset($requestData['checklist_id']) ? $requestData['checklist_id'] : null,
                    
                ];            
                return Image::create($saveArr);
            }
        }
        return null;
    }

    public function delete()
    {
        // delete all related photos 
        $pathImage = Image::getImagePath($this->file, $this->type);
        if (Storage::disk(Image::$storage)->exists($pathImage));
            Storage::disk(Image::$storage)->delete($pathImage);

        $pathThumb = Image::getImagePath($this->file, $this->type, true);
        if (Storage::disk(Image::$storage)->exists($pathThumb));
            Storage::disk(Image::$storage)->delete($pathThumb);

        // delete the photo
        return parent::delete();
    }
    
}
