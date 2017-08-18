<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    protected $fillable = ['hive_id', 'frame_id', 'category_id', 'name', 'queen_cell_stage', 'queen_cell_type', 'perc_brood', 'perc_honey', 'perc_pollen', 'perc_wax', 'weight_kg', 'brood_perc_all_stages', 'brood_perc_open', 'brood_perc_queen', 'brood_perc_drone', 'brood_perc_worker', 'pattern_score'];
	protected $guarded 	= ['id'];
    protected $hidden   = ['hive_id', 'frame_id', 'category_id'];
    protected $appends  = ['type'];

    public $timestamps = false;
    
    // Relations
    public function getTypeAttribute()
    {
        return Category::find($this->category_id)->name;
    }
    
	public function hive()
    {
        return $this->hasOne(Hive::class);
    }

    public function frame()
    {
        return $this->hasOne(HiveLayerFrame::class, 'frame_id');
    }
}
