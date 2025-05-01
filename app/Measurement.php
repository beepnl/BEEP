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
     * data_source_type     : type of data source $data_types
     * data_api_url         : (external) url to call data from
     * data_repository_url  : Github repository URL with explanation, and background of measurement source
     *
     * @var array
     */
    protected $fillable              = ['abbreviation', 'physical_quantity_id', 'show_in_charts', 'chart_group', 'min_value', 'max_value', 'hex_color', 'show_in_alerts', 'show_in_dials', 'weather', 'data_source_type', 'data_api_url', 'data_repository_url', 'future'];
    protected $hidden                = ['created_at', 'updated_at']; //'parent'
    protected $appends               = ['pq','unit','pq_name_unit', 'low_value', 'high_value']; //'parent'
    public static $data_source_types = ['db_influx'=>'Influx Database', 'api'=>'API', 'lambda_model'=>'Lambda Model', 'open_weather'=>'Open Weather'];

    public static function boot()
    {
        parent::boot();

        static::created(function($m)
        {
            $m->forgetCache();
        });

        static::updated(function($m)
        {
            $m->forgetCache();
        });

        static::deleted(function($m)
        {
            $m->forgetCache();
        });
    }

    public function forgetCache()
    {
        Cache::forget('api-gateway-valid_measurements');
        Cache::forget('measurements-pq-abbr');
        Cache::forget('measurements-pq-abbr-weather');
        Cache::forget('measurements-pq-abbr-weather-show-in-charts');
        Cache::forget('measurements-pq-abbr-no-weather');
        Cache::forget('measurements-pq-abbr-no-weather-show-in-charts');
        Cache::forget('measurements-min-max-values');
        Cache::forget('measurement-list-weather-output-names');
        Cache::forget('measurement-list-weather-output-pq');
        Cache::forget('measurement-list-weather-valid-names');
        Cache::forget('measurement-list-weather-valid-pq');
        Cache::forget('measurement-list-sensors-output-names');
        Cache::forget('measurement-list-sensors-output-pq');
        Cache::forget('measurement-list-sensors-valid-names');
        Cache::forget('measurement-list-sensors-valid-pq');
        Cache::forget('measurement-list-weather-output-names-hide-grade');
        Cache::forget('measurement-list-weather-output-pq-hide-grade');
        Cache::forget('measurement-list-weather-valid-names-hide-grade');
        Cache::forget('measurement-list-weather-valid-pq-hide-grade');
        Cache::forget('measurement-list-sensors-output-names-hide-grade');
        Cache::forget('measurement-list-sensors-output-pq-hide-grade');
        Cache::forget('measurement-list-sensors-valid-names-hide-grade');
        Cache::forget('measurement-list-sensors-valid-pq-hide-grade');
        Cache::forget('measurement-'.$this->id.'-trans-'.$this->abbreviation);
        Cache::forget('measurement-'.$this->id.'-pq-'.$this->physical_quantity_id);
        Cache::forget('measurement-'.$this->id.'-pq-'.$this->physical_quantity_id.'-'.$this->abbreviation.'-name');
        Cache::forget('measurement-'.$this->id.'-pq-'.$this->physical_quantity_id.'-unit');
        Cache::forget('meas-id-'.$this->id.'-abbr'); // fot SensorDefinition
        Cache::forget('meas-id-'.$this->id.'-min'); // fot SensorDefinition
        Cache::forget('meas-id-'.$this->id.'-max'); // fot SensorDefinition
        Cache::forget('measurement-display-decimals');
        Category::forgetTaxonomyListCache();
        if (isset($this->physical_quantity_id))
            $this->physical_quantity->forgetCache();
    }


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

    private function physical_quantity_cached()
    {
        return Cache::remember('measurement-'.$this->id.'-pq-'.$this->physical_quantity_id, env('CACHE_TIMEOUT_LONG'), function () {
            return $this->physical_quantity;
        });
    }

    public function pq_name()
    {
        // add sensor name (temporarily)
        return Cache::remember('measurement-'.$this->id.'-pq-'.$this->physical_quantity_id.'-'.$this->abbreviation.'-name', env('CACHE_TIMEOUT_LONG'), function () {
            //$abbr = '';
            // if (isset($this->abbreviation))
            // {
            //     $mabb = $this->abbreviation;
            //     $aind = strpos($mabb, '_'); 
            //     $abbr = ' - '.($aind ? substr($mabb, 0, $aind) : $mabb);
            // }
            return $this->physical_quantity_cached()->name; //.$abbr;
        });
    }

    public function unit()
    {
        return Cache::remember('measurement-'.$this->id.'-pq-'.$this->physical_quantity_id.'-unit', env('CACHE_TIMEOUT_LONG'), function () {
            $pq = $this->physical_quantity_cached();
            if ($pq)
                return $pq->unit;
            else
                return '';
        });
    }
    
    public function pq_name_unit()
    {
        if ($this->physical_quantity_id != null)
        {
            $unit = $this->unit() != null && $this->unit() != '' ? ' ('.$this->unit().')' : '';
            $name = $this->pq_name().$unit;
            if ($name)
                return $name;
        }
        return null;
    }

    public function trans()
    {
        //$trans = Translation::where('name', $this->name)->with('language')->get()->pluck('translation','language.lang');
        $out = Cache::rememberForever('measurement-'.$this->id.'-trans-'.$this->abbreviation, function () {
            $trans = DB::table('translations')
                    ->join('languages', 'translations.language_id', '=', 'languages.id')
                    ->where('translations.type', 'measurement')
                    ->where('translations.name', $this->abbreviation)
                    ->select('translations.translation', 'languages.twochar')
                    ->get();
            
            if ($trans)
            {
                $out = [];
                foreach($trans as $item)
                    $out[$item->twochar] = $item->translation; 
                
                if (count($out) > 0)
                    return $out;
            }
            return null;
        });

        if ($out)
            return $out;

        return null;
    }

    public function getAbbrNamedObjectAttribute()
    {
        return $this->toArray();
    }

    public static function getIdByAbbreviation($abbreviation)
    {
        $measurement_abbr_ids = Cache::remember('measurement-abbr-ids', env('CACHE_TIMEOUT_LONG'), function ()
        {
            return Measurement::pluck('id', 'abbreviation')->toArray();
        });
        if (isset($measurement_abbr_ids[$abbreviation]))
            return $measurement_abbr_ids[$abbreviation];

        return null;
    }

    public static function getMatchingMeasurements()
    {
        return ['bv','w_v','weight_kg', 't_i','t_0','t_1','s_bin_71_122','s_bin_122_173','s_bin_173_224','s_bin_224_276','s_bin_276_327','s_bin_327_378','s_bin_378_429','s_bin_429_480','s_bin_480_532','s_bin_532_583','s_bin_0_201','s_bin_201_402','s_bin_402_602','s_bin_602_803','s_bin_803_1004','s_bin_1004_1205','s_bin_1205_1406','s_bin_1406_1607','s_bin_1607_1807','s_bin_1807_2008'];
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

    public static function getWeightMeasurementIds()
    {
        return Cache::remember('measurement-weight-ids', env('CACHE_TIMEOUT_LONG'), function ()
        {
            $input_id  = Measurement::where('abbreviation','w_v')->value('id');
            $output_id = Measurement::where('abbreviation','weight_kg')->value('id');
            return ['input_id'=>$input_id, 'output_id'=>$output_id];
        });
    }

    public static function minMaxValuesArray()
    {
        return Cache::rememberForever('measurements-min-max-values', function () {
            $out = [];
            $measurements = self::all();
            foreach ($measurements as $m) {
                $out[$m->abbreviation] = ['min' => $m->min_value, 'max' => $m->max_value];
            }

            return $out;
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
