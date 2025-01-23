<?php

namespace App;

use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use LaravelLocalization;

use Auth;
use Cache;
use Moment\Moment;

class Inspection extends Model
{
    use SoftDeletes, CascadeSoftDeletes;
    protected $cascadeDeletes = ['items'];
    
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'inspections';

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
    protected $fillable = ['notes', 'created_at', 'impression', 'attention', 'reminder', 'reminder_date', 'checklist_id', 'image_id'];

    protected $hidden   = ['pivot','deleted_at', 'hives', 'locations', 'items'];

    protected $appends  = ['owner', 'thumb_url', 'hive_id', 'location_id', 'item_count', 'searchable'];

    public $timestamps = false;


    public static function boot()
    {
        parent::boot();

        static::created(function ($i) {
            //Log::info("Created Inspection $i->id");
            $i->empty_cache();
        });

        static::updated(function($i)
        {
            $i->empty_cache(false);
        });

        static::deleted(function($i)
        {
            $i->empty_cache();
        });
    }

    // Cache functions
    public function empty_cache($clear_users=true)
    {
        Cache::forget('inspection-'.$this->id.'-hive-ids');
        Cache::forget('inspection-'.$this->id.'-location-ids');
        Cache::forget('inspection-'.$this->id.'-item-count');
        Cache::forget('inspection-'.$this->id.'-searchable-array');
        
        Log::debug("inspection ID $this->id cache emptied");

        foreach ($this->hives as $hive)
            $hive->empty_cache(false);

        if ($clear_users)
        {
            $user_ids = $this->users()->pluck('id')->toArray();
            foreach ($user_ids as $uid) {
                User::emptyIdCache($uid, 'inspection');
            }
        }

    }



    public function getOwnerAttribute()
    {
        if ($this->users()->whereIn('id', [Auth::user()->id])->count() > 0)
            return true;
        
        return false;
    }

    public function getThumbUrlAttribute()
    {
        if (isset($this->image_id))
            return $this->image->thumb_url;

        return null;
    }

    public function getHiveIdsAttribute()
    {
        return Cache::rememberForever('inspection-'.$this->id.'-hive-ids', function ()
        {
            return $this->hives()->pluck('id')->toArray();
        });
    }

    public function getHiveIdAttribute()
    {
        $hive_ids = $this->getHiveIdsAttribute();
        return count($hive_ids) > 0 ? $hive_ids[0] : null;
    }

    public function getLocationIdsAttribute()
    {
        return Cache::rememberForever('inspection-'.$this->id.'-location-ids', function ()
        {
            return $this->locations()->pluck('id')->toArray();
        });
    }

    public function getLocationIdAttribute()
    {
        $loc_ids = $this->getLocationIdsAttribute();
        return count($loc_ids) > 0 ? $loc_ids[0] : null;
    }

    public function getItemCountAttribute()
    {
        return Cache::rememberForever('inspection-'.$this->id.'-item-count', function ()
        {
            return $this->items()->count();
        });
    }

    public function getSearchableAttribute()
    {
        return Cache::rememberForever('inspection-'.$this->id.'-searchable-array', function ()
        {
            return $this->items->whereIn('type', ['text', 'sample_code', 'date'])->pluck('value')->toArray();
        });
    }

    // Relations
    public function users()
    {
        return $this->belongsToMany(User::class, 'inspection_user');
    }

    public function hives()
    {
        return $this->belongsToMany(Hive::class, 'inspection_hive');
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'inspection_location');
    }

    public function items()
    {
        return $this->hasMany(InspectionItem::class);
    }

    public function checklist()
    {
        return $this->hasOne(Checklist::class);
    }

    public function image()
    {
        return $this->belongsTo(Image::class);
    }
    
    public function delete()
    {
        // delete image 
        if(isset($this->image_id))
            $this->image()->delete();

        // delete the research
        return parent::delete();
    }

    public static function createInspection($items=[], $hive_ids=null, $location_ids=null, $notes='', $timeZone="UTC")
    {
        $now                            = new Moment();
        $inspection_data                = [];
        $inspection_data['created_at']  = $now->setTimezone($timeZone)->format('Y-m-d H:i');
        $inspection_data['notes']       = $notes;
        $inspection_data['items']       = $items;

        $inspection = Inspection::create($inspection_data);
        foreach ($inspection_data['items'] as $cat_id => $value) 
        {
            $itemData = 
            [
                'category_id'   => $cat_id,
                'inspection_id' => $inspection->id,
                'value'         => $value,
            ];
            InspectionItem::create($itemData);
        }

        $inspection->users()->sync(Auth::user()->id);

        if (isset($hive_ids))
            $inspection->hives()->sync($hive_ids);

        if (isset($location_ids))
            $inspection->locations()->sync($location_ids);

        return $inspection;
    }

    public static function item_names($inspections, $include_inspection_items=false) // get a locale ordered list of InspectionItem names
    {
        $locale          = LaravelLocalization::getCurrentLocale();
        $inspection_ids  = $inspections->pluck('id')->toArray();
        $inspection_items= InspectionItem::whereIn('inspection_id',$inspection_ids)->groupBy('category_id')->get(); // let the newest id be selected, if multiple on one day
        $inspection_objs = Inspection::whereIn('id', $inspection_ids)->orderBy('created_at', 'desc')->get();

        //die(print_r([$include_inspection_items, $inspections->toArray(), $inspection_items->toArray()]));

        $item_names = [];
        foreach ($inspection_items as $item)
        { 
            $cat_id = $item->category_id;
            $cat    = $item->category;
            // Commented out to enable showing 'system' category inspection items
            // if ($cat->isSystem())
            //     continue;
            //die(print_r($item->toArray()));
            
            if ($include_inspection_items)
            {
                $arr = [];
                $set = false;
                foreach ($inspection_objs as $d => $inspection) 
                {   
                    $inspection_all_items = $inspection->items;//->with('name')->orderBy('name', 'asc')->get();
                    $arr[$d] = '';
                    //die(print_r($inspection_all_items));
                    foreach ($inspection_all_items as $inspection_item) 
                    {
                        if ($inspection_item->category_id == $cat_id)
                        {
                            $arr[$d] = $inspection_item;
                            $set = true;
                            continue;
                        }
                    }
                }
                if ($set && isset($cat))
                    $item_names[] = ['anc' => $cat->ancName($locale), 'name' => $cat->transName($locale), 'type'=>$cat->input, 'range'=>$cat->inputRange(), 'items' => $arr];
            } 
            else if (isset($cat))
            {
                $item_names[] = ['anc' => $cat->ancName($locale), 'name' => $cat->transName($locale), 'type'=>$cat->input, 'range'=>$cat->inputRange()];
            }

        }

        usort($item_names, function($a,$b){ return strcasecmp($a['anc'].$a['name'], $b['anc'].$b['name']); } ); // place items in alphabetical order

        //die(print_r($item_names));
        return $item_names;
    }
}
