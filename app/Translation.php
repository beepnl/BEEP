<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    public $fillable = ['name','language_id','type','translation'];

    public $timestamps = false;

    // Relations

    public function language()
    {
        return $this->hasOne(Language::class, 'id', 'language_id');
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
}
