<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Moment\Moment;
use App\Category;
use App\Hive;
use App\Location;
use LaravelLocalization;

class CategoryInput extends Model
{
    protected $table = 'category_inputs';

    public $fillable = ['name','type','min','max','decimals','icon'];

    public $hidden = ['icon'];

    public $timestamps = false;

    // Relations

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function name_plus()
    {
        $out = $this->name;

        if (isset($this->min))
            $out .= ' | min:'.$this->min;

        if (isset($this->max))
            $out .= ' | max:'.$this->max;

        if (isset($this->decimals))
            $out .= ' | decimals:'.$this->decimals;

        return $out;
    }

    public static function getTypeId($type)
    {
        return CategoryInput::where('type', $type)->value('id');
    }


    public static function selectList()
    {
    	$list = [];

    	foreach(CategoryInput::orderBy('name')->get() as $ci)
			$list[$ci->id] = $ci->name_plus();
		
		return $list;
    }

    public function render($val, $locale = null)
    {
        if (!isset($val) || $val === null)
            return null;

        if ($locale == null)
            $locale = LaravelLocalization::getCurrentLocale();

        $intVal     = intval($val);
        $type_array = __('taxonomy.'.$this->type);

        switch($this->type)
        {
            case 'select':
            case 'options':
            case 'list_item':
                $name = null;
                $cat = Category::find($intVal);
                if ($cat)
                    $name = $cat->transName($locale);

                if ($name)
                    $val = $name;

                break;

            case 'list':
                $optionNames = [];
                foreach(explode(',', $val) as $option)
                {
                    $name = null;
                    $cat = Category::find($option);
                    if ($cat)
                        $name = $cat->transName($locale);

                    if ($name)
                        array_push($optionNames, $name);
                }
                $val = implode(',',$optionNames);
                break;

            case 'date':
                if (isset($val) && $val != null)
                {
                    $time = strtotime($val);
                    $val = date('Y-m-d H:i:s', $time);
                }
                break;

            case 'boolean':
            case 'boolean_yes_red':
            case 'score_quality':
            case 'score_amount':
            case 'smileys_3':
                if ($intVal > -1 && $intVal < count($type_array))
                    $val = $type_array[$intVal];

                break;

            case 'select_hive':
                $hive = Hive::find($intVal);
                if ($hive)
                    $val = $hive->name;

                break;

            case 'select_apiary':
            case 'select_location':
                $loc = Location::find($intVal);
                if ($loc)
                    $val = $loc->name;

                break;

        }
        return $val;
    }

    public function glyphIcon()
    {
    	$icons = 
    	[
            'boolean'   => 'ok-sign',
            'boolean_yes_red' => 'remove-sign',
            'date'      => 'calendar',
            'list'      => 'list-alt',
            'list_item' => 'unchecked',
            'number'    => 'stats',
            'number_0_decimals' => 'stats',
            'number_1_decimals' => 'stats',
            'number_2_decimals' => 'stats',
            'number_3_decimals' => 'stats',
            'number_negative'   => 'stats',
            'number_percentage' => 'resize-horizontal',
            'number_positive'   => 'stats',
            'options'   => 'record',
            'text'      => 'align-left',
            'barcode'   => 'barcode',
            'bee_subspecies' => 'queen',
            'color_picker'   => 'eyedropper',
            'file'      => 'save-file',
            'grade'     => 'bookmark',
            'image'     => 'picture',
            'label'     => 'tag',
            'number_degrees'    => 'view-360',
            'score'     => 'star-empty',
            'score_amount'  => 'star-empty',
            'score_quality' => 'star-empty',
            'select'    => 'collapse-down',
            'select_apiary'=> 'th',
            'select_continent'=> 'globe',
            'select_country'=> 'globe',
            'select_hive'    => 'credit-card',
            'select_hive_layer' => 'credit-card',
            'select_hive_layer_frame' => 'credit-card',
            'select_location'=> 'map-marker',
            'slider'    => 'minus',
            'smileys_3' => 'star',
    	];

    	return isset($icons[$this->type]) ? $icons[$this->type] : 'alert';
    }

}

