<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\PhysicalQuantity;
use App\Translation;
use Cache;
use LaravelLocalization;

class Measurement extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'measurements';

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
    protected $fillable = ['abbreviation', 'physical_quantity_id', 'show_in_charts', 'chart_group', 'min_value', 'max_value', 'hex_color', 'show_in_alerts', 'show_in_dials', 'weather'];

    protected $hidden  = ['created_at', 'updated_at']; //'parent'

    protected $appends  = ['pq','unit','pq_name_unit', 'low_value', 'high_value']; //'parent'


    public function getPqAttribute()
    {
        return $this->pq_name();
    }

    public function getUnitAttribute()
    {
        return $this->unit();
    }

    public function getPqNameUnitAttribute()
    {
        return $this->pq_name_unit();
    }

    public function getLowValueAttribute()
    {
        return $this->physical_quantity()->value('low_value');
    }

    public function getHighValueAttribute()
    {
        return $this->physical_quantity()->value('high_value');
    }

    public function physical_quantity()
    {
        return $this->hasOne(PhysicalQuantity::class, 'id', 'physical_quantity_id');
    }

    public function transName($locale = null)
    {
        $trans = Translation::translate($this->abbreviation, null, false, 'measurement');
        return isset($trans) ? $trans : $this->name;
    }

    public function pq_name($translate = true)
    {
        // add sensor name (temporarily)
        $trans = $translate ? Translation::translate($this->abbreviation, null, false, 'measurement') : null;
        $name  = isset($trans) && strtolower($trans) != $this->abbreviation ? $trans : $this->physical_quantity()->value('name');
        // if (($name != '' && $name != '-') && isset($this->abbreviation))
        // {
        //     $abbr = '';
        //     $mabb = $this->abbreviation;
        //     $aind = strpos($mabb, '_'); 
        //     $abbr = ' - '.($aind ? substr($mabb, 0, $aind) : $mabb);
        //     $name .= $abbr;
        // }
        // else 
        if ($name == '-' && isset($this->abbreviation))
        {
            $name = str_replace('_', ' ', $this->abbreviation);
        }
        return $name;
    }

    public function unit()
    {
        return $this->physical_quantity()->value('unit');
    }
    
    public function pq_name_unit($translate = true)
    {
        if ($this->physical_quantity_id != null)
        {
            $unit = $this->unit() != null && $this->unit() != '' && $this->unit() != '-' ? ' ('.$this->unit().')' : '';
            $name = $this->pq_name($translate).$unit;
            if ($name)
                return $name;
        }
        return null;
    }

    public function getAbbrNamedObjectAttribute()
    {
        return $this->toArray();
    }

    public static function getIdByAbbreviation($abbreviation)
    {
        $m = Measurement::where('abbreviation', $abbreviation)->first();
        if ($m)
            return $m->id;

        return null;
    }

    public static function getMatchingMeasurements()
    {
        return ['bv','w_v','weight_kg','t_i','t_0','t_1','s_bin_71_122','s_bin_122_173','s_bin_173_224','s_bin_224_276','s_bin_276_327','s_bin_327_378','s_bin_378_429','s_bin_429_480','s_bin_480_532','s_bin_532_583'];
    }

    public static function getValidMeasurements($output=false, $weather=false, $locale=null)
    {
        $name_table = $weather ? 'weather' : 'sensors';
        $name_value = $output ? 'output' : 'valid';
        $locale     = $locale == null ? LaravelLocalization::getCurrentLocale() : $locale;
        return Cache::remember('measurement-list-'.$locale.'-'.$name_table.'-'.$name_value, env('CACHE_TIMEOUT_LONG'), function () use ($output, $weather)
        { 
            if ($output)
                return Measurement::where('weather',$weather)->where('show_in_charts', true)->pluck('abbreviation')->toArray();

            return Measurement::where('weather',$weather)->get()->pluck('pq', 'abbreviation')->toArray();
        });
    }

    public static function selectList()
    {
        $list = [];
        $list[''] = '-';

        foreach(Measurement::orderBy('abbreviation')->get() as $q)
        {
            $id = $q->id;
            $label = $q->abbreviation.' ('.$q->pq_name_unit.')';

            $list[$id] = $label;

        }
        return $list;
    }
}
