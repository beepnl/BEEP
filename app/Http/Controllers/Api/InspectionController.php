<?php

namespace App\Http\Controllers\Api;

use App\Condition;
use App\Category;
use App\Hive;
use App\HiveType;
use App\BeeRace;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Moment\Moment;
use Auth;

class InspectionController extends Controller
{
    
    public function lists()
    {
        $out                = [];
        // $condition_cat_id   = Category::typeName('base_category','condition')->pluck('id');
        // $out['conditions']  = Category::where('parent_id',$condition_cat_id)->with('children')->get();

        // $action_cat_id      = Category::typeName('base_category','action')->pluck('id');
        // $out['actions']     = Category::where('parent_id',$action_cat_id)->with('children')->get();

        $out['categories']  = Category::where('type','base_category')->with('children.children')->get();
        $out['beeraces']    = BeeRace::all();
        $out['hivetypes']   = HiveType::all();

        return response()->json($out);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $hive_id)
    {
        $hive = $request->user()->hives()->findOrFail($hive_id);
        
        // Get the available dates
        $dates = $hive->conditions()->orderBy('created_at', 'desc')->pluck('created_at');
        $dates = $dates->merge($hive->actions()->orderBy('created_at', 'desc')->pluck('created_at'));

        $dates_array = $dates->unique()->toArray();
        
        // Get rid of the keys
        $unique_dates = [];
        foreach ($dates_array as $value) 
        {
            $unique_dates[] = $value;
        }
        //die(print_r($unique_dates));

        $dates_data = [];
        foreach ($unique_dates as $i => $date) 
        {
            $dates_data[$i]               = [];
            $dates_data[$i]['date']       = $date;
            $dates_data[$i]['conditions'] = $hive->conditions()->orderBy('id', 'desc')->where('created_at',$date)->get()->unique('category_id')->toArray(); 
            $dates_data[$i]['actions']    = $hive->actions()->orderBy('id', 'desc')->where('created_at',$date)->get()->unique('category_id')->toArray(); 
        }

        //die(print_r($dates_data));

        $conditions         = $hive->conditions()->groupBy('category_id')->pluck('category_id')->toArray(); // let the newest id be selected, if multiple on one day
        $conditions_by_date = [];
        foreach ($conditions as $id)
        { 
            $arr = [];
            $set = '';
            foreach ($dates_data as $d => $data) 
            {
                $arr[$d] = '';
                foreach ($data['conditions'] as $c) 
                {
                    if ($id == $c['category_id'])
                    {
                        $arr[$d] = $c;
                        $set = $c['name'];
                    }
                }

            }
            if ($set != '')
                $conditions_by_date[$set] = $arr;
        }
        //ksort($conditions_by_date);

        $actions         = $hive->actions()->groupBy('category_id')->pluck('category_id')->toArray(); // let the newest id be selected, if multiple on one day
        $actions_by_date = [];
        foreach ($actions as $id)
        { 
            $arr = [];
            $set = '';
            foreach ($dates_data as $d => $data) 
            {
                $arr[$d] = '';
                foreach ($data['actions'] as $c) 
                {
                    if ($id == $c['category_id'])
                    {
                        $arr[$d] = $c;
                        $set = $c['name'];
                    }
                }

            }
            //if ($set != '')
                $actions_by_date[$set] = $arr;
        }
        //ksort($actions_by_date);

        return response()->json(['dates'=>$unique_dates, 'conditions'=>$conditions_by_date, 'actions'=>$actions_by_date]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $hive_id, $date)
    {
        $hive       = $request->user()->hives()->findOrFail($hive_id);
        $conditions = $hive->conditions()->where('hive_id',$hive_id)->where('created_at', $date)->delete();
        $actions    = $hive->actions()->where('hive_id',$hive_id)->where('created_at', $date)->delete();
    }

}
