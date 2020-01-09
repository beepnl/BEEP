<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use InterventionImage;
use Storage;
use Auth;

class Image extends Model
{
    
    public static $storage      = 'public';
    public static $maxPizelSize = 2000;
    public static $thumbPixels  = 200;
    public static $thumbQuality = 70;
    public static $imageDir     = 'images';
    public static $thumbDir     = 'thumbs';

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
    protected $fillable = ['filename', 'image_url', 'thumb_url', 'description', 'type', 'height', 'width', 'size_kb', 'date', 'user_id', 'hive_id', 'category_id', 'inspection_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function hive()
    {
        return $this->belongsTo(Hive::class);
    }
    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }


    // Static functions
    public static function imageUrl($filename, $type)
    {
        return Storage::disk(Image::$storage)->url(Image::getImagePath($filename, $type));
    }

    public static function imageThumbUrl($filename, $type)
    {
        return Storage::disk(Image::$storage)->url(Image::getImagePath($filename, $type, true));
    }

    public static function getImagePath($fileName, $type='inspection', $thumb=false)
    {
        $dir = $thumb ? Image::$thumbDir : Image::$imageDir;
        $uid = Auth::user()->id;
        return 'users/'.$uid.'/'.$dir.'/'.$type.'/'.$fileName;
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
                    'filename'    => $fileName,
                    'image_url'   => Image::imageUrl($fileName, $type),
                    'thumb_url'   => Image::imageThumbUrl($fileName, $type),
                    'description' => isset($requestData['description']) ? $requestData['description'] : null,
                    'type'        => $type,
                    'height'      => $imageHeight,
                    'width'       => $imageWidth,
                    'size_kb'     => round($fileSize/1024),
                    'date'        => $fileTimeStamp,
                    'user_id'     => Auth::user()->id,
                    'hive_id'     => isset($requestData['hive_id']) ? $requestData['hive_id'] : null,
                    'category_id' => isset($requestData['category_id']) ? $requestData['category_id'] : null,
                    'inspection_id'=> isset($requestData['inspection_id']) ? $requestData['inspection_id'] : null,
                    
                ];            
                return Image::create($saveArr);
            }
        }
        return null;
    }

    public function delete()
    {
        // delete all related photos 
        $pathImage = Image::getImagePath($this->filename, $this->type);
        if (Storage::disk(Image::$storage)->exists($pathImage));
            Storage::disk(Image::$storage)->delete($pathImage);

        $pathThumb = Image::getImagePath($this->filename, $this->type, true);
        if (Storage::disk(Image::$storage)->exists($pathThumb));
            Storage::disk(Image::$storage)->delete($pathThumb);

        // delete the photo
        return parent::delete();
    }
    
}
