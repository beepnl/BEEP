<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Response;
use App\Setting;
use App\Hive;

class HiveController extends Controller
{
    public function store(Request $request)
    {
    	$user_id = $request->user()->id;
    	$hive_id = $request->input('hive_id');
		$type	 = $request->exists('type') ? $request->input('type') : 'spaarkast';
		$name 	 = $request->exists('name') ? $request->input('name') : 'Kast '.(1+Hive::where('user_id', $user_id)->count());

		$hive    = Hive::updateOrCreate(['id'=>$hive_id, 'user_id'=>$user_id], ['type'=>$type, 'name'=>$name]);

    	return Response::json($hive);
    }
    
    public function index(Request $request)
    {
    	$user_id = $request->user()->id;
    	$hive_id = $request->has('hive_id') ? $request->input('hive_id') : null;
    	
    	$hives= Hive::where('user_id', $user_id)
    					->orderBy('type', 'asc')
			    		->orderBy('name', 'asc')
			    		->get();

		foreach ($hives as $key => $hive) 
		{
			$settings = Setting::where('hive_id', $hive->id)
    					->orderBy('type', 'asc')
			    		->orderBy('name', 'asc')
			    		->get();

			$hives[$key]['settings'] = $settings;
		}

    	return Response::json($hives);
    }
}
