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
    
    protected $hidden   = ['category','deleted_at','inspection_id'];

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

    public function inputType()
    {
        if (isset($this->category))
            return $this->category->inputType;

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
        $val   = $this->value;
        $input = $this->inputType();

        if (!isset($val) || $val === null || $input === null)
            return null;

        return $input->render($val, $locale);
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
