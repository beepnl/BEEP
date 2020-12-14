<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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

    public static function getTypeId($type)
    {
        return CategoryInput::where('type', $type)->value('id');
    }

    public static function selectList()
    {
    	$list = [];

    	foreach(CategoryInput::orderBy('name')->get() as $ci)
		{
			$list[$ci->id] = $ci->name;

			if (isset($ci->min))
				$list[$ci->id] .= ' | min:'.$ci->min;

			if (isset($ci->max))
				$list[$ci->id] .= ' | max:'.$ci->max;

			if (isset($ci->decimals))
				$list[$ci->id] .= ' | decimals:'.$ci->decimals;

		}
		return $list;
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

