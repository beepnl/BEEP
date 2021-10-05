<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use LaravelLocalization;
use Moment\Moment;

class InspectionItem extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'inspection_items';

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
    protected $fillable = ['value', 'inspection_id', 'category_id'];

    protected $appends  = ['val','unit','type'];
    
    protected $hidden   = ['category','deleted_at'];

    public $timestamps = false;

    public function getNameAttribute()
    {
        return $this->name();
    }
    public function getAncAttribute()
    {
        return $this->ancestors();
    }
    public function getValAttribute()
    {
        return $this->val();
    }
    public function getUnitAttribute()
    {
        return $this->unit();
    }
    public function getTypeAttribute()
    {
        return $this->type();
    }

    public function type()
    {
        if (isset($this->category))
            return $this->category->input;

        return null;
    }

    public function inspection()
    {
        return $this->hasOne(Inspection::class, 'id', 'inspection_id');
    }

    public function category()
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }

    public function name()
    {
        if (isset($this->category))
            return $this->category->transName();

        return '';
    }

    public function ancestors()
    {
        if (isset($this->category))
            return $this->category->ancName();

        return '';
    }

    public function val($locale = null)
    {
        $val = $this->value;

        if (!isset($val) || $val == null)
            return null;

        if ($locale == null)
            $locale = LaravelLocalization::getCurrentLocale();

        $intVal = $val;

        $amounts  = __('taxonomy.amounts');
        $quality  = __('taxonomy.quality');
        $smileys  = __('taxonomy.smileys');
        $boolean  = __('taxonomy.boolean');

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
                    $moment = new Moment($val);
                    $val = $moment->format('Y-m-d H:i:s');
                }
                break;

            case 'boolean':
            case 'boolean_yes_red':
                if ($intVal > -1 && $intVal < count($boolean))
                    $val = $boolean[$intVal];

                break;

            case 'score_quality':
                if ($intVal > -1 && $intVal < count($quality))
                    $val = $quality[$intVal];
                break;

            case 'score_amounts':
                if ($intVal > -1 && $intVal < count($amounts))
                    $val = $amounts[$intVal];
                break;

            case 'smileys_3':
                if ($intVal > -1 && $intVal < count($smileys))
                    $val = $smileys[$intVal];
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

    public function unit()
    {
        if (isset($this->category))
            return $this->category->unit;

        return '';
    }

    public function humanReadableValue()
    {
        $value = $this->val;
        
        if (isset($this->unit))
            $value .= ' '.$this->unit;

        return $value;
    }


    
}
