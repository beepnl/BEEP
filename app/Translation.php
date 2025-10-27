<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use LaravelLocalization;

class Translation extends Model
{
    public $fillable = ['name','language_id','type','translation'];

    public $timestamps = false;

    public static function boot()
    {
        parent::boot();

        static::created(function($t)
        {
            $t->forgetCache();
        });

        static::updated(function($t)
        {
            $t->forgetCache();
        });

        static::deleted(function($t)
        {
            $t->forgetCache();
        });
    }

    public function forgetCache()
    {
        Category::forgetTaxonomyListCache();
    }

    // Relations

    public function language()
    {
        return $this->hasOne(Language::class, 'id', 'language_id');
    }


    // static functions
    public static function translate($name, $locale=null, $array=false, $type='category')
    {
        if ($locale == null && $array == false)
            $locale = LaravelLocalization::getCurrentLocale();

        $trans = DB::table('translations')
                    ->join('languages', 'translations.language_id', '=', 'languages.id')
                    ->where('translations.type', $type)
                    ->where('translations.name', $name)
                    ->select('translations.translation', 'languages.twochar')
                    ->get();

        if ($trans)
        {
            $out = [];
            foreach($trans as $item)
            {
                if ($array == false && isset($locale) && $item->twochar == $locale)
                    return $item->translation;

                $out[$item->twochar] = $item->translation; 
            }
            
            if ($array == false)
                return ucfirst(str_replace('_', ' ', $name));
            else
                return $out;
        }
        return null;
    }

    public static function translateArray($name)
    {
        return Translation::translate($name, null, true);
    }

    public static function saveText($language_abbr, $name, $type, $text)
    {
    	if (isset($text))
    	{
	    	$lang_id = Language::where('abbreviation', $language_abbr)->value('id');

	    	if (!isset($lang_id) || $lang_id == null)
	    		return false;

			$count   = Translation::where('name', $name)->where('type', $type)->where('language_id', $lang_id)->count();

	    	if ($count > 0)
	    	{
	    		if (Translation::where('name', $name)->where('type', $type)->where('language_id', $lang_id)->value('translation') == $text)
	    			return false;
	    			
	    		$trans = Translation::where('name', $name)->where('type', $type)->where('language_id', $lang_id)->update(['translation' => $text]);
	    	}
	    	else
	    	{
	    		$trans = new Translation;
	    		$trans->language_id = $lang_id;
	    		$trans->name = $name;
	    		$trans->type = $type;
				$trans->translation = $text;
				$trans->save();
	    	}
			return true;
	    }
	    return false;
    }

    public static function get($locale, $name, $type)
    {
        $lang_id = Language::where('twochar', strtolower($locale))->value('id');
        //dd($lang_id, $locale, $name, $type);
        if (empty($lang_id))
            return '';

        return Cache::remember('trans-lang-'.$locale.'-type-'.$type.'-name-'.$name, env('CACHE_TIMEOUT_LONG'), function () use ($name, $type, $lang_id)
        {
            return self::where('name', $name)->where('type', $type)->where('language_id', $lang_id)->value('translation');
        });
    }

    public static function createTranslations($name, $type)
    {
        $trans = ucfirst(str_replace('_', ' ', $name));
        $count = 0;

        foreach (Language::all()->pluck('abbreviation') as $abbr) 
        {
            $lang_id = Language::where('abbreviation', $abbr)->value('id');
            if (Translation::where('name',$name)->where('type','category')->where('language_id',$lang_id)->count() == 0)
            {
                Translation::create(['language_id'=>$lang_id, 'name'=>$name, 'type'=>'category', 'translation'=>$trans]);
                $count++;
            }
        }

        return $count;
    }
}
