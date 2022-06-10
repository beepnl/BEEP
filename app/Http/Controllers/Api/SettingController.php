<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Response;
use App\Setting;
use App\Hive;
use App\Category;

/**
 * @group Api\SettingController
 * @authenticated
 */
class SettingController extends Controller
{
    public function store(Request $request)
    {
    	$user_id = $request->user()->id;
        $type    = $request->input('type');
        $number  = $request->input('id');

        $input   = $request->input();
        if ($request->filled('id'))
            unset($input['id']);

        //die(print_r($input));
        // $cat_id  = Category::name($type);
        // if (isset($cat_id))
        // {
            $save_cnt = 0;
            foreach($input as $name => $value)
            {
                if ($name == "")
                    continue;

                if ($request->filled('id'))
                {
                    $request->user()->settings()->where('name',$name)->where('number',$number)->delete();
                }
                else
                {
                    $request->user()->settings()->where('name',$name)->delete();
                }

                $setting          = new Setting;
                $setting->category_id = 0;
                $setting->name    = $name;
                $setting->value   = $value;
                $setting->number  = $number;
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
    	//die(print_r($request->user()->settings()->get()));
        return Response::json($request->user()->settings()->groupBy('name','number')->get());
    }


}
