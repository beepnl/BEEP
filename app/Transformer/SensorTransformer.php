<?php
 
namespace App\Transformer;
use League\Fractal;
 
class SensorTransformer {
 
    public function transform($sensor) // works with array in stead of Collection (Object) 
    {
	    $valid_sensors = [
	    	't' => ['name'=>'temperature', 'unit'=>'°C'],
	    	'h' => ['name'=>'humidity', 'unit'=>'%RV'],
	    	'p' => ['name'=>'air_pressure', 'unit'=>'mbar'],
	    	'w' => ['name'=>'weight_sum', 'unit'=>''],
	    	'l' => ['name'=>'light', 'unit'=>'lux'],
	    	'bv' => ['name'=>'bat_volt', 'unit'=>'mV'],
	    	'weight_kg' => ['name'=>'weight_kg', 'unit'=>'Kg'],
	    	'weight_kg_corrected' => ['name'=>'weight_kg_corrected', 'unit'=>'Kg'],
            'w_o' => ['name'=>'weight_offset', 'unit'=>'Kg'],
            's_fan_4' => ['name'=>'sound_fanning_4days', 'unit'=>''],
            's_fan_6' => ['name'=>'sound_fanning_6days', 'unit'=>''],
            's_fan_9' => ['name'=>'sound_fanning_9days', 'unit'=>''],
            's_fly_a' => ['name'=>'sound_flying_adult', 'unit'=>''],
            's_tot' => ['name'=>'sound_total', 'unit'=>''],
            'bc_i' => ['name'=>'bee_count_in', 'unit'=>'#'],
            'bc_o' => ['name'=>'bee_count_out', 'unit'=>'#'],
            't_i' => ['name'=>'t_i', 'unit'=>'°C'],
            'snr' => ['name'=>'snr', 'unit'=>'dB'],
            'rssi' => ['name'=>'rssi', 'unit'=>'dBm'],
	    ];

	    $name = $sensor['name'];
	    $valid= in_array($name, array_keys($valid_sensors));
	    
	    return [
            'name' 	=> $valid ? $valid_sensors[$name]['name'] : $sensor['name'],
            'value' => isset($sensor['value']) ? $sensor['value'] : "",
            'unit' 	=> $valid ? $valid_sensors[$name]['unit'] : "",
            'time'  => isset($sensor['time']) ? $sensor['time'] : 0,
            //'key'   => isset($sensor['key']) ? $sensor['key'] : ""
        ];
	    
	}
 
}