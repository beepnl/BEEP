<?php

use Illuminate\Database\Seeder;
use App\Measurement;
use App\PhysicalQuantity;

class MeasurementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    protected $valid_sensors = [
            't'         => 'temperature',
            'h'         => 'humidity',
            'p'         => 'air_pressure',
            'w'         => 'weight_sum',
            'l'         => 'light',
            'bv'        => 'bat_volt',
            'w_v'       => 'weight_combined_kg',
            'w_fl'      => 'weight_front_left',
            'w_fr'      => 'weight_front_right',
            'w_bl'      => 'weight_back_left',
            'w_br'      => 'weight_back_right',
            's_fan_4'   => 'sound_fanning_4days',
            's_fan_6'   => 'sound_fanning_6days',
            's_fan_9'   => 'sound_fanning_9days',
            's_fly_a'   => 'sound_flying_adult',
            's_tot'     => 'sound_total',
            't_i'       => 'temp_inside',
            'bc_i'      => 'bee_count_in',
            'bc_o'      => 'bee_count_out',
            'weight_kg_noOutlier' => 'weight_kg_noOutlier',
            'weight_kg' => 'weight_kg',
            'weight_kg_corrected' => 'weight_kg_corrected',
            'rssi'      => 'rssi',
            'snr'       => 'snr',
            'lat'       => 'lat',
            'lon'       => 'lon',
            's_bin098_146Hz' => '098_146Hz',
            's_bin146_195Hz' => '146_195Hz',
            's_bin195_244Hz' => '195_244Hz',
            's_bin244_293Hz' => '244_293Hz',
            's_bin293_342Hz' => '293_342Hz',
            's_bin342_391Hz' => '342_391Hz',
            's_bin391_439Hz' => '391_439Hz',
            's_bin439_488Hz' => '439_488Hz',
            's_bin488_537Hz' => '488_537Hz',
            's_bin537_586Hz' => '537_586Hz',    
            'calibrating_weight' => 'calibrating_weight',
            'w_fl_kg_per_val'    => 'w_fl_kg_per_val',    
            'w_fr_kg_per_val'    => 'w_fr_kg_per_val',    
            'w_bl_kg_per_val'    => 'w_bl_kg_per_val',    
            'w_br_kg_per_val'    => 'w_br_kg_per_val',    
            'w_fl_offset'        => 'w_fl_offset',    
            'w_fr_offset'        => 'w_fr_offset',    
            'w_bl_offset'        => 'w_bl_offset',    
            'w_br_offset'        => 'w_br_offset',  
    ];
    protected $output_sensors = [
            't',
            'h',
            'p',
            'l',
            'bv',
            's_fan_4',
            's_fan_6',
            's_fan_9',
            's_fly_a',
            's_tot',
            't_i',
            'bc_i',
            'bc_o',
            'weight_kg_noOutlier',
            'weight_kg',
            'weight_kg_corrected',
            'rssi',
            'snr',
            'lat',
            'lon',
            's_bin098_146Hz',
            's_bin146_195Hz',
            's_bin195_244Hz',
            's_bin244_293Hz',
            's_bin293_342Hz',
            's_bin342_391Hz',
            's_bin391_439Hz',
            's_bin439_488Hz',
            's_bin488_537Hz',
            's_bin537_586Hz',     
        ];

    public function run()
    {
        if (PhysicalQuantity::where('abbreviation','-')->count() == 0)
        {
        	PhysicalQuantity::create(['abbreviation'=>'hPa','name'=>'Pressure','unit'=>'hPa']);
        	PhysicalQuantity::create(['abbreviation'=>'V','name'=>'Voltage','unit'=>'V']);
        	$pq = PhysicalQuantity::create(['abbreviation'=>'-','name'=>'-','unit'=>'-']);
        }
        else
        {
        	$pq = PhysicalQuantity::where('name','-')->first();
        }

        foreach ($this->valid_sensors as $abbr => $long) 
        {
        	$name = ucfirst(str_replace('_', ' ', $long));
        	if (Measurement::where('abbreviation',$abbr)->count() == 0)
        	{
        		$pq_id = $pq->id;
        		$show  = in_array($abbr, $this->output_sensors);

        		switch($abbr)
        		{
        			case 't':
        			case 'h':
        				$pq_id = PhysicalQuantity::where('name',$name)->first()->id;
        				break;
        			case 'weight_kg':
        			case 'weight_kg_corrected':
                    case 'weight_kg_noOutlier':
        				$pq_id = PhysicalQuantity::where('unit','kg')->first()->id;
        				break;
        			case 't_i':
        				$pq_id = PhysicalQuantity::where('name','Temperature')->first()->id;
        				break;
        			case 'bv':
        				$pq_id = PhysicalQuantity::where('name','Voltage')->first()->id;
        				break;
        			case 'p':
        				$pq_id = PhysicalQuantity::where('name','Pressure')->first()->id;
        				break;
        		}
        		$data  = ['abbreviation'=>$abbr, 'show_in_charts'=>$show, 'physical_quantity_id'=>$pq_id];
        		Measurement::create($data);
        	}
        }
    }
}
