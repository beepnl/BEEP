<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Cache;
use DB;
use LaravelLocalization;

class PhysicalQuantity extends Model
{
    protected $table    = 'physical_quantities';
    protected $appends  = ['trans'];

    public $fillable    = ['name','unit','abbreviation','low_value','high_value'];

    public $timestamps = false;

    public static function boot()
    {
        parent::boot();

        static::created(function($p)
        {
            $p->forgetCache();
        });

        static::updated(function($p)
        {
            $p->forgetCache();
        });

        static::deleted(function($p)
        {
            $p->forgetCache();
        });
    }


    public function forgetCache()
    {
        Cache::forget('measurements-pq-abbr');
        Cache::forget('pq-'.$this->id.'-trans-'.$this->abbreviation);
        Cache::forget('pq-'.$this->id.'-measurements-abbr-use-in-alerts-0');
        Cache::forget('pq-'.$this->id.'-measurements-abbr-use-in-alerts-1');

        $locales = array_keys(config('laravellocalization.supportedLocales'));
        foreach ($locales as $locale) 
            Cache::forget('pq-trans-'.$this->id.'-'.$locale.'-name');

        Category::forgetTaxonomyListCache();
    }

    // Relations
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function measurements()
    {
        return $this->hasMany(Measurement::class);
    }

    public function getUnitAttribute()
    {
        return !empty($this->attributes['unit']) ? $this->attributes['unit'] : '';
    }

    public function pq_name_unit()
    {
        if (isset($this->unit))
            return $this->name.' ('.$this->unit.')';

        return $this->name;
    }

    public function trans($locale = null)
    {
        //$trans = Translation::where('name', $this->name)->with('language')->get()->pluck('translation','language.lang');
        $out = Cache::rememberForever('pq-'.$this->id.'-trans-'.$this->abbreviation, function () {
            $trans = DB::table('translations')
                    ->join('languages', 'translations.language_id', '=', 'languages.id')
                    ->where('translations.type', 'physical_quantity')
                    ->where('translations.name', $this->abbreviation)
                    ->select('translations.translation', 'languages.twochar')
                    ->get();
            
            if ($trans)
            {

                $out = [];
                foreach($trans as $item)
                {
                    if (isset($locale) && $item->twochar == $locale)
                        return $item->translation;
                    
                    $out[$item->twochar] = $item->translation; 
                }
                return $out;
            }
        });

        if ($out)
            return $out;

        return null;
    }

    public function transName($locale = null)
    {
        if ($locale == null)
            $locale = LaravelLocalization::getCurrentLocale();
        
        return Cache::rememberForever('pq-trans-'.$this->id.'-'.$locale.'-name', function () use ($locale){
            $trans = $this->trans();
            return isset($trans[$locale]) ? $trans[$locale] : $this->name;
        });

    }


    public function getTransAttribute()
    {
        return $this->trans();
    }

    public static function selectList()
    {
    	$list = [];
    	$list[''] = '-';

    	foreach(PhysicalQuantity::orderBy('name')->get() as $q)
		{
            $id = $q->id;
            $label = $q->name.' ('.$q->unit.')';
			if (isset($q->abbreviation))
				$label .= ' - '.$q->abbreviation;

            $list[$id] = $label;

		}
        return $list;
    }
}
