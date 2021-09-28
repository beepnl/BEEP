<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Moment\Moment;
use InterventionImage;
use Storage;
use Auth;

class Image extends Model
{
    
    public static $storage      = 'public';
    public static $maxPizelSize = 2000; // pixel hight/width to fit images in
    public static $thumbPixels  = 200; // pixel hight/width to fit images in
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
    protected $fillable = ['storage', 'filename', 'image_url', 'thumb_url', 'description', 'type', 'height', 'width', 'size_kb', 'date', 'user_id', 'hive_id', 'category_id', 'inspection_id'];

    protected $hidden   = ['storage', 'user_id'];

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
        $storage      = env('IMAGE_STORAGE', Image::$storage);
        $url          = Storage::disk($storage)->url(Image::getImagePath($filename, $type));
        if ($storage == 'public')
            $url = env('APP_URL').$url;

        return $url;
    }

    public static function imageThumbUrl($filename, $type)
    {
        $storage      = env('IMAGE_STORAGE', Image::$storage);
        $url          = Storage::disk($storage)->url(Image::getImagePath($filename, $type, true));
        
        if ($storage == 'public')
            $url = env('APP_URL').$url;

        return $url;
    }

    public static function getImagePath($fileName, $type='inspection', $thumb=false)
    {
        $imageDir     = env('IMAGE_FULL_DIRECTORY', Image::$imageDir);
        $thumbDir     = env('IMAGE_THUMB_DIRECTORY', Image::$thumbDir);

        $dir = $thumb ? $thumbDir : $imageDir;
        $uid = Auth::user()->id;
        return 'users/'.$uid.'/'.$dir.'/'.$type.'/'.$fileName;
    }

    public static function formatExifDate($exifDateString) // from 2020:01:04 11:20:18 -> Y-m-d H:i:s, format is already in UTC
    {
        $dateTimeArray = explode(' ', $exifDateString);    // 2020:01:04
        $dateArray     = explode(':', $dateTimeArray[0]);  // 2020 01 04

        if (count($dateArray) == 3 && checkdate($dateArray[1], $dateArray[2], $dateArray[0]))
        {
            $dateString = implode('-', $dateArray);        // 2020-01-04
            $dateTime   = $dateString.'T'.$dateTimeArray[1];
            $m = new Moment($dateTime, 'UTC');
            return $m->setTimezone('UTC')->format('Y-m-d H:i:s');
        }
        return null;
    }

    public static function store($requestData, $type='inspection')
    {
        // check if image needs to be updated, or newly created
        $image  = null;
        
        if(isset($requestData['id']))
        {
            $image = Auth::user()->images()->findOrFail($requestData['id']);
        }

        $storage      = env('IMAGE_STORAGE', Image::$storage);
        $maxPizelSize = env('IMAGE_RESIZE_PIXELS', Image::$maxPizelSize);
        $thumbPixels  = env('IMAGE_THUMB_PIXELS', Image::$thumbPixels);
        $thumbQuality = env('IMAGE_THUMB_QUALITY', Image::$thumbQuality);
        $imageDir     = env('IMAGE_FULL_DIRECTORY', Image::$imageDir);
        $thumbDir     = env('IMAGE_THUMB_DIRECTORY', Image::$thumbDir);

        $anu = function($constraint)
        { 
            $constraint->aspectRatio(); 
            $constraint->upsize(); 
        };

        //get file extension
        if(isset($requestData['file']) || isset($requestData['image']))
        {
            $imageFile = isset($requestData['image']) && $requestData['image']->isValid() ? $requestData['image'] : $requestData['file'];

            if ($imageFile->isValid())
            {
                //filename to store
                $extension = $imageFile->getClientOriginalExtension();
                $fileName  = Str::random(60).'.'.$extension;
                $imagePath = Image::getImagePath($fileName, $type);
                $thumbPath = Image::getImagePath($fileName, $type, true);

                // save big image
                $imageResized= InterventionImage::make($imageFile)->resize($maxPizelSize, $maxPizelSize, $anu);
                $fileDate    = Image::formatExifDate($imageResized->exif('DateTimeOriginal')); // formed as 2020:01:04 11:20:18
                $imageHeight = $imageResized->getHeight();
                $imageWidth  = $imageResized->getWidth();
                $imageStored = Storage::disk($storage)->put($imagePath, $imageResized->stream());
                $fileSize    = Storage::disk($storage)->size($imagePath);
                // $fileTime    = Storage::disk($storage)->lastModified($thumbPath);
                $fileDate    = $fileDate ? $fileDate : date('Y-m-d H:i:s');
                
                // save thumbnail
                $thumb       = InterventionImage::make($imageResized)->resize($thumbPixels, $thumbPixels, $anu);
                $thumbStored = Storage::disk($storage)->put($thumbPath, $thumb->stream());
                
                if ($imageStored)
                {
                    $saveArr = [
                        'storage'     => $storage,
                        'filename'    => $fileName,
                        'image_url'   => Image::imageUrl($fileName, $type),
                        'thumb_url'   => Image::imageThumbUrl($fileName, $type),
                        'description' => isset($requestData['description']) ? $requestData['description'] : null,
                        'type'        => $type,
                        'height'      => $imageHeight,
                        'width'       => $imageWidth,
                        'size_kb'     => round($fileSize/1024),
                        'date'        => $fileDate,
                        'user_id'     => Auth::user()->id,
                        'hive_id'     => isset($requestData['hive_id']) ? $requestData['hive_id'] : null,
                        'category_id' => isset($requestData['category_id']) ? $requestData['category_id'] : null,
                        'inspection_id'=> isset($requestData['inspection_id']) ? $requestData['inspection_id'] : null,
                        
                    ];           

                    
                    if($image)
                    {
                        return $image->update($saveArr);
                    }

                    return Image::create($saveArr);
                }
            }
        }
        return null;
    }

    public function delete()
    {
        // delete all related photos 
        $storage = isset($this->storage) ? $this->storage : env('IMAGE_STORAGE', Image::$storage);

        $pathImage = Image::getImagePath($this->filename, $this->type);
        if (Storage::disk($storage)->exists($pathImage));
            Storage::disk($storage)->delete($pathImage);

        $pathThumb = Image::getImagePath($this->filename, $this->type, true);
        if (Storage::disk($storage)->exists($pathThumb));
            Storage::disk($storage)->delete($pathThumb);

        // delete the photo
        return parent::delete();
    }
    
}
