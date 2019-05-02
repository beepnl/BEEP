<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\PhysicalQuantity;

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
    protected $fillable = ['abbreviation', 'physical_quantity_id', 'show_in_charts', 'chart_group', 'min_value', 'max_value', 'hex_color'];

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

    public function pq_name()
    {
        // add sensor name (temporarily)
        
        $name = $this->physical_quantity()->value('name');
        if (($name != '' && $name != '-') && isset($this->abbreviation))
        {
            $abbr = '';
            $mabb = $this->abbreviation;
            $aind = strpos($mabb, '_'); 
            $abbr = ' - '.($aind ? substr($mabb, 0, $aind) : $mabb);
            $name .= $abbr;
        }
        return $name;
    }

    public function unit()
    {
        return $this->physical_quantity()->value('unit');
    }
    
    public function pq_name_unit()
    {
        if ($this->physical_quantity_id != null)
        {
            $unit = $this->unit() != null && $this->unit() != '' && $this->unit() != '-' ? ' ('.$this->unit().')' : '';
            $name = $this->pq_name().$unit;
            if ($name)
                return $name;
        }
        return null;
    }

    public function getAbbrNamedObjectAttribute()
    {
        return $this->toArray();
    }
}
