<?php

namespace App;

use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hive extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    protected $cascadeDeletes = ['queen','inspections','layers','frames','productions'];
    protected $fillable = ['user_id', 'location_id', 'hive_type_id', 'color', 'name'];
    protected $guarded  = ['id'];
	protected $hidden 	= ['user_id'];
    protected $appends  = ['type','location','attention','impression','reminder','reminder_date','inspection_count'];

    public $timestamps = false;

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

    public function getAttentionAttribute()
    {
        return $this->getLastInspectionItem('attention');
    }

    public function getImpressionAttribute()
    {
        return $this->getLastInspectionItem('impression');
    }

    public function getNoteAttribute()
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

    private function getLastInspectionItem($name)
    {
        $item = $this->inspections()->orderBy('created_at','desc')->first();
        if (isset($item[$name]))
            return $item[$name];

        return null;
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
    
    // manually inserted items
    public function conditions()
    {
        return $this->hasMany(Condition::class);
    }

    public function actions()
    {
        return $this->hasMany(Action::class);
    }

    public function productions()
    {
        return $this->hasMany(Production::class);
    }

    public function inspectionDates()
    {
        $dates = $this->conditions()->pluck('created_at');
        $dates = $dates->merge($this->actions()->pluck('created_at'));

        if ($dates)
        {
            $dates_array = $dates->unique();
            return $dates_array;
        }
        return collect([]);
    }

    public function inspections_by_date()
    {
        return $this->inspections()->orderBy('created_at', 'desc')->get();
    }

    public function inspection_items_by_date()
    {
        // Get the available dates
        $inspections   = $this->inspections_by_date();
        $items_by_date = Inspection::item_names($inspections);

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
}
