<?php

namespace App;

use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

use Auth;

class Hive extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    protected $cascadeDeletes = ['queen','inspections','layers','frames'];
    protected $fillable = ['user_id', 'location_id', 'hive_type_id', 'color', 'name', 'bb_width_cm', 'bb_depth_cm', 'bb_height_cm', 'fr_width_cm', 'fr_height_cm', 'order'];
    protected $guarded  = ['id'];
	protected $hidden 	= ['user_id','deleted_at'];
    protected $appends  = ['type','location','attention','impression','notes','reminder','reminder_date','inspection_count','sensors','owner','editable','group_ids','last_inspection_date'];

    public $timestamps = false;

    public static function boot()
    {
        parent::boot();

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
            
        });
    }


	// Relations
	public function getTypeAttribute()
    {
        return Category::find($this->hive_type_id)->name;
    }

    public function getLocationAttribute()
    {
        $loc = Location::find($this->location_id);
        return isset($loc) ? $loc->name : '';
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
        return $this->layers()->where('category_id', Category::findCategoryIdByParentAndName('hive_layer','honey'))->count();
    }

    public function getBroodlayersAttribute()
    {
        return $this->layers()->where('category_id', Category::findCategoryIdByParentAndName('hive_layer','brood'))->count();
    }

    public function getFeedingBoxAttribute()
    {
        return $this->layers()->where('category_id', Category::findCategoryIdByParentAndName('hive_layer','feeding_box'))->count();
    }

    public function getQueenExcluderAttribute()
    {
        return $this->layers()->where('category_id', Category::findCategoryIdByParentAndName('hive_layer','queen_excluder'))->count();
    }

    public function getSensorsAttribute()
    {
        return $this->devices()->pluck('id')->toArray();
    }

    public function getNameAndLocationAttribute()
    {
        $out  = $this->name;
        $out .= isset($this->location_id) ? ' - '.$this->getLocationAttribute() : '';
        return $out;
    }

    private function getLastInspectionItem($name)
    {
        $item = $this->inspections()->orderBy('created_at','desc')->first();
        if (isset($item[$name]))
            return $item[$name];

        return null;
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

    public function inspections_by_date()
    {
        return $this->inspections()->orderBy('created_at', 'desc')->get();
    }

    public function inspection_items_by_date()
    {
        // Get the available dates
        $inspections   = $this->inspections_by_date();
        $items_by_date = Inspection::item_names($inspections, true);

        // Add category header
        for ($i=count($items_by_date)-1; $i >= 0; $i--)
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

        return $items_by_date;
    }

    public static function selectList($onlyMine=false)
    {
        if ($onlyMine)
            return Auth::user()->hives()->orderBy('name')->pluck('name','id');
        else
            return Hive::orderBy('name')->pluck('name','id');
    }

}
