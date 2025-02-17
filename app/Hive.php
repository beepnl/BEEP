<?php

namespace App;

use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\InspectionCollection;
use Auth;
use Cache;

class Hive extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    protected $cascadeDeletes = ['queen','inspections','layers','frames'];
    protected $fillable = ['user_id', 'location_id', 'hive_type_id', 'color', 'name', 'bb_width_cm', 'bb_depth_cm', 'bb_height_cm', 'fr_width_cm', 'fr_height_cm', 'order'];
    protected $guarded  = ['id'];
	protected $hidden 	= ['user_id','deleted_at','pivot'];
    protected $appends  = ['type','location','attention','impression','notes','reminder','reminder_date','inspection_count','sensors','owner','editable','group_ids','last_inspection_date'];

    public $timestamps = false;

    public static function boot()
    {
        parent::boot();

        static::created(function($h)
        {   
            $h->empty_cache();
        });

        static::updated(function($h)
        {   
            $h->empty_cache();
        });

        static::deleting(function($h)
        {
            // remove device-hive link
            foreach ($h->devices as $d)
            {
                $d->hive_id = null;
                $d->save();
            };
            // remove hive id from AlertRules exclude_hive_ids
            $ars = $h->user->alert_rules;
            if (isset($ars) && count($ars) > 0)
            {
                foreach ($ars as $a)
                    $a->remove_hive_id_from_exclude_hive_ids($h->id);
            }
            $h->empty_cache();
        });
    }

    public function empty_cache($clear_user=true)
    {
        Cache::forget("hive-$this->id-layer-count-honey");
        Cache::forget("hive-$this->id-layer-count-brood");
        Cache::forget("hive-$this->id-layer-count-feeding_box");
        Cache::forget("hive-$this->id-layer-count-queen_excluder");
        Cache::forget("hive-$this->id-location-name");
        Cache::forget("hive-$this->id-last-inspection-item");

        Log::debug("Hive ID $this->id cache emptied");

        foreach($this->groups as $group)
            $group->empty_cache(true);

        if ($clear_user)
            User::emptyIdCache($this->user_id, 'apiary');
    }

	// Relations
	public function getTypeAttribute()
    {
        return Cache::rememberForever("hive-type-$this->hive_type_id-name", function () {
            return Category::find($this->hive_type_id)->name;
        });
    }

    public function getLocationAttribute()
    {
        $loc_name = '';
        if (isset($this->location_id))
        {
            $cache_name = "hive-$this->id-location-name";
            $location   = Location::find($this->location_id);
            $loc_name   = Cache::rememberForever($cache_name, function () use ($location) {
                return isset($location->name) ? $location->name : '';
            });
            if ($loc_name == '')
                Cache::forget($cache_name);
        }
        return $loc_name;
    }

    public function getLastInspectionDateAttribute()
    {
        return $this->getLastInspectionItem('created_at');
    }

    public function getAttentionAttribute()
    {
        return $this->getLastInspectionItem('attention');
    }

    public function getImpressionAttribute()
    {
        return $this->getLastInspectionItem('impression');
    }

    public function getNotesAttribute()
    {
        return $this->getLastInspectionItem('notes');
    }

    public function getReminderAttribute()
    {
        return $this->getLastInspectionItem('reminder');
    }

    public function getReminderDateAttribute()
    {
        return $this->getLastInspectionItem('reminder_date');
    }

    public function getInspectionCountAttribute()
    {
        return $this->inspections()->count();
    }

    public function getHoneylayersAttribute()
    {
        $cat_id = Cache::rememberForever('hive-layer-type-id-honey', function () {
            return Category::findCategoryIdByParentAndName('hive_layer','honey');
        });
        return  Cache::rememberForever("hive-$this->id-layer-count-honey", function () use ($cat_id) {
            return $this->layers()->where('category_id', $cat_id)->count();
        });
    }

    public function getBroodlayersAttribute()
    {
        $cat_id = Cache::rememberForever('hive-layer-type-id-brood', function () {
            return Category::findCategoryIdByParentAndName('hive_layer','brood');
        });
        return  Cache::rememberForever("hive-$this->id-layer-count-brood", function () use ($cat_id) {
            return $this->layers()->where('category_id', $cat_id)->count();
        });
    }

    public function getFeedingBoxAttribute()
    {
        $cat_id = Cache::rememberForever('hive-layer-type-id-feeding_box', function () {
            return Category::findCategoryIdByParentAndName('hive_layer','feeding_box');
        });
        return  Cache::rememberForever("hive-$this->id-layer-count-feeding_box", function () use ($cat_id) {
            return $this->layers()->where('category_id', $cat_id)->count();
        });
    }

    public function getQueenExcluderAttribute()
    {
        $cat_id = Cache::rememberForever('hive-layer-type-id-queen_excluder', function () {
            return Category::findCategoryIdByParentAndName('hive_layer','queen_excluder');
        });
        return  Cache::rememberForever("hive-$this->id-layer-count-queen_excluder", function () use ($cat_id) {
            return $this->layers()->where('category_id', $cat_id)->count();
        });
    }

    public function getSensorsAttribute()
    {
        return $this->devices()->pluck('id')->toArray();
    }

    public function hasDevices()
    {
        return $this->devices()->count() > 0 ? true : false;
    }

    public function getNameAndLocationAttribute()
    {
        $out  = $this->name;
        $out .= isset($this->location_id) ? ' - '.$this->getLocationAttribute() : '';
        return $out;
    }

    private function getLastInspectionItem($name)
    {
        $inspection = Cache::remember("hive-$this->id-last-inspection-item", 5, function () {
            return $this->inspections()->orderBy('created_at','desc')->first();
        });
        if (isset($inspection->{$name}))
            return $inspection->{$name};
    }

    public function getAllInspectionDates()
    {
        $item = $this->inspections()-> pluck('created_at')->toArray();
        
        return $item;
    }

    public function getGroupIdsAttribute()
    {
        return $this->groups()->pluck('group_id')->toArray();
    }

    public function getOwnerAttribute()
    {
        if (Auth::check() && $this->user_id == Auth::user()->id)
            return true;
        
        return false;
    }

    public function getEditableAttribute()
    {
        // removed editable for owner, because this is already implied in ownership
        // if ($this->getOwnerAttribute())
        //     return true;
        
        if (Auth::check())
        {
            $user_editable_hive_ids = Auth::user()->groupHives(true)->pluck('id')->toArray();
            return in_array($this->id, $user_editable_hive_ids);
        }
        return false;
    }



    public function queen()
    {
        return $this->hasOne(Queen::class);
    }

    public function type()
    {
        return $this->belongsTo(Category::class, 'hive_type_id');
    }
	
	public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_hive');
    }

    public function checklists()
    {
        return $this->belongsToMany(Checklist::class, 'checklist_hive');
    }

    public function inspections()
    {
        return $this->belongsToMany(Inspection::class, 'inspection_hive');
    }

    // Hive buildup
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function layers()
    {
        return $this->hasMany(HiveLayer::class);
    }

    public function frames()
    {
        return $this->hasManyThrough(HiveLayerFrame::class, HiveLayer::class, 'hive_id', 'layer_id');
    }
    
    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    public function inspections_by_date($request)
    {
        $search      = $request->filled('search') ? $request->input('search') : null;
        $attention   = $request->filled('attention') ? boolval($request->input('attention')) : null;
        $reminder    = $request->filled('reminder') ? boolval($request->input('reminder')) : null;
        $impression  = $request->filled('impression') ? explode(',', $request->input('impression')) : null;
        $id          = $request->filled('id') ? $request->input('id') : null;

        $inspections = $this->inspections;

        if (isset($id))
        {
            $inspections = $inspections->where('id', $id);
        }
        else
        {
            if ($request->filled('start'))
                $inspections = $inspections->where('created_at', '>=', $request->input('start'));

            if ($request->filled('end'))
                $inspections = $inspections->where('created_at', '<=', $request->input('end'));

            if (!empty($search)) {
                $inspections = $inspections->filter(function($item) use ($search){
                    $match = 0;
                    $match += stristr($item->notes, $search) !== false ? 1 : 0;
                    $match += stristr($item->reminder, $search) !== false ? 1 : 0;
                    $match += stristr($item->created_at, $search) !== false ? 1 : 0;
                    $match += stristr($item->reminder_date, $search) !== false ? 1 : 0;
                    // Include searching in (translated) inspection item values
                    $inspection_item_values = $item->items->pluck('val')->toArray();
                    $match += stristr(implode(',', $inspection_item_values), $search) !== false ? 1 : 0;
                    // Include searching in (translated) inspection item names
                    $inspection_item_names = $item->items->pluck('name')->toArray();
                    $match += stristr(implode(',', $inspection_item_names), $search) !== false ? 1 : 0;

                    return $match > 0 ? true : false;
                });
            }

            if (isset($attention))
                $inspections = $inspections->where('attention', $attention);

            if (isset($reminder))
                $inspections = $inspections->whereNotNull('reminder');

            if (isset($impression))
                $inspections = $inspections->whereIn('impression', $impression);
            
        }

        //die(print_r(['search'=>$search, 'id'=>$id, 'ins'=>$inspections->toArray()]));
        return $inspections->sortByDesc('created_at');
    }

    public function inspection_items_by_date($request, $locale, $paginated_result=true)
    {
        // Get the available dates
        $paginated_result = boolval($request->input('paginated_result', $paginated_result));
        $page_index       = $request->filled('page') ? $request->input('page') : 1;
        $items_per_page   = intval($request->input('items_per_page', 5));
        
        $inspect_coll     = $this->inspections_by_date($request);
        //dd($inspect_coll);
        $inspections      = $inspect_coll->paginate(env('INSPECTIONS_PER_PAGE', $items_per_page), $page_index, true);
        $items_by_date    = Inspection::item_names($inspections, true);

        // Add category header
        for ($i=count($items_by_date)-1; $i >= 0; $i--) // must be processed backwards, because items are added
        {
            $item       = $items_by_date[$i];
            $rootName   = explode(' > ', $item['anc'])[0];
            $piRootName = $i == 0 ? null : explode(' > ', $items_by_date[$i-1]['anc'])[0];
            if ($piRootName == null || $piRootName != $rootName)
            {
                $spliceIndex  = $i == 0 ? 0 : $i;
                array_splice($items_by_date, $spliceIndex, 0, [['anc' => null, 'name' => $rootName, 'items' => null]]);
            }
        }

        if ($paginated_result === false && count($inspections->items()) > 0)
            $inspections = $inspections->toArray()['data'];

        return ['inspections'=>$inspections, 'items_by_date'=>$items_by_date];
    }

    public static function selectList($onlyMine=false)
    {
        if ($onlyMine)
            return Auth::user()->hives()->orderBy('name')->pluck('name','id');
        else
            return Hive::orderBy('name')->pluck('name','id');
    }

}
