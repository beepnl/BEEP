<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Response;
use App\Setting;
use App\Hive;
use App\Category;

class SettingController extends Controller
{
    public function store(Request $request)
    {
    	$user_id = $request->user()->id;
        $type    = $request->input('type');

        // $cat_id  = Category::name($type);
        // if (isset($cat_id))
        // {
            $save_cnt = 0;
            foreach($request->input() as $name => $value)
            {
                if ($name == "")
                    continue;

                $setting          = new Setting;
                $setting->category_id = 0;
                $setting->name    = $name;
                $setting->value   = $value;
                $saved = $request->user()->settings()->save($setting);
                
                if ($saved)
                    $save_cnt++;

            }

            if ($save_cnt > 0)
            {
        	   return $this->index($request);
            }
        //}

        return Response::json('no named settings to save', 400);
    }
    
    public function index(Request $request)
    {
    	//die($request->user());
        return Response::json($request->user()->settings());
    }

}
