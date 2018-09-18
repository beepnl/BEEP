<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PhysicalQuantity extends Model
{
    protected $table = 'physical_quantities';

    public $fillable = ['name','unit','abbreviation'];

    public $timestamps = false;

    // Relations

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
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
