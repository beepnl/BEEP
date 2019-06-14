<?php

namespace App\Http\Controllers\Api;

use App\Hive;
use App\Queen;
use App\Category;
use App\Location;
use App\HiveFactory;
use Illuminate\Http\Request;
use App\Http\Requests\PostHiveRequest;
use App\Http\Controllers\Controller;

class HiveController extends Controller
{
    /**
     * @var HiveFactory
    **/
    private $hiveFactory;

    public function __construct(HiveFactory $hiveFactory)
    {
        $this->hiveFactory = $hiveFactory;
    }

    private function saveQueen(Request $request, $hive)
    {
        if ($request->filled('queen.race_id') || $request->filled('queen.name') || $request->filled('queen.created_at') || $request->filled('queen.color') || $request->filled('queen.clipped') || $request->filled('queen.fertilized'))
        {
            $race_id = Category::findCategoryIdByParentAndName('subspecies', 'other');
            $date  = $request->filled('queen.created_at') ? $request->input('queen.created_at') : date("Y-m-d");
            $queen = [
                    'name'          =>$request->input('queen.name'),
                    'race_id'       =>$request->input('queen.race_id', $race_id),
                    'created_at'    =>$date.' 00:00:00',
                    'color'         =>$request->input('queen.color'),
                    'clipped'       =>boolval($request->input('queen.clipped')),
                    'fertilized'    =>boolval($request->input('queen.fertilized')),
                ];

            $hive->queen()->updateOrCreate(['id'=>$request->input('queen.id', null)], $queen);
        }
        return $hive;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->user()->hives()->count() > 0)
            return response()->json(['hives'=>$request->user()->hives()->with('layers.frames', 'queen')->get()]);

        return response()->json(['error'=>'no hives available'],404);
    }

  
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PostHiveRequest $request)
    {
        
        $user_id          = $request->user()->id;
        $location         = $request->user()->locations()->findOrFail($request->input('location_id'));
        $name             = $request->input('name'); 
        $hive_type_id     = $request->input('hive_type_id', 63); 
        $color            = $request->input('color', '#FABB13'); // yellow
        $broodLayerAmount = $request->input('brood_layers', 1);
        $honeyLayerAmount = $request->input('honey_layers', 1);
        $frameAmount      = $request->input('frames', 10);

        $hive = $this->hiveFactory->createHive($user_id, $location, $name, $hive_type_id, $color, $broodLayerAmount, $honeyLayerAmount, $frameAmount);
        $hive = $this->saveQueen($request, $hive);

        return $this->show($request, $hive);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Hive  $hive
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Hive $hive)
    {
        return response()->json(['hives'=>[$request->user()->allhives()->orderBy('name')->with('layers.frames', 'queen')->findOrFail($hive->id)]]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Hive  $hive
     * @return \Illuminate\Http\Response
     */
    public function update(PostHiveRequest $request, Hive $hive)
    {
        $hive             = $request->user()->allhives(true)->findOrFail($hive->id);
        $location         = $request->user()->allLocations(true)->findOrFail($request->input('location_id'));
        $name             = $request->input('name'); 
        $hive_type_id     = $request->input('hive_type_id'); 
        $color            = $request->input('color', '#FABB13'); // yellow
        $broodLayerAmount = $request->input('brood_layers', 1);
        $honeyLayerAmount = $request->input('honey_layers', 1);
        $frameAmount      = $request->input('frames', 10);

        $hive = $this->hiveFactory->updateHive($hive, $location, $name, $hive_type_id, $color, $broodLayerAmount, $honeyLayerAmount, $frameAmount);
        $hive = $this->saveQueen($request, $hive);

        return $this->show($request, $hive);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Hive  $hive
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Hive $hive)
    {
        $hive = $request->user()->hives()->findOrFail($hive->id);
        $hive->delete();
    }
}
