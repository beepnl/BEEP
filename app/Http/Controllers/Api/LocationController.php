<?php

namespace App\Http\Controllers\Api;

use App\Location;
use App\Continent;
use App\Category;
use App\HiveFactory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LocationController extends Controller
{
    /**
     * @var HiveFactory
    **/
    private $hiveFactory;

    public function __construct(HiveFactory $hiveFactory)
    {
        $this->hiveFactory = $hiveFactory;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return response()->json(['locations'=>$request->user()->locations()->with('hives.layers.frames', 'hives.queen')->get()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $name             = $request->input('name'); 
        $prefix           = $request->has('prefix') == false && isset($name)? $name : $request->input('prefix'); 
        $continent        = Continent::where('abbr', $request->input('continent','eu'))->first();
        $category         = Category::findCategoryByParentAndName('location', $request->input('location_type','fixed'))->first();
        $location         = new Location([
                'name'          =>$name, 
                'continent_id'  =>$continent->id, 
                'category_id'   =>isset($category->id) ? $category->id : null,
                'coordinate_lat'=>$request->input('lat', 52),
                'coordinate_lon'=>$request->input('lon', 5),
                'city'          =>$request->input('city'),
                'street'        =>$request->input('street'),
                'street_no'     =>$request->input('street_no'),
                'postal_code'   =>$request->input('postal_code'),
                'country_code'  =>$request->input('country_code', 'nl'),
            ]);

        $request->user()->locations()->save($location);
        
        $user_id          = $request->user()->id;
        $amount           = $request->input('hive_amount'); 
        $count_start      = intval($request->input('offset')); // 1
        $hive_type_id     = $request->input('hive_type_id'); 
        $color            = $request->input('color', '#FABB13'); // yellow
        $broodLayerAmount = $request->input('brood_layers', 1);
        $honeyLayerAmount = $request->input('honey_layers', 1);
        $frameAmount      = $request->input('frames', 10);

        $hives = $this->hiveFactory->createMultipleHives($user_id, $amount, $location, $prefix, $hive_type_id, $color, $broodLayerAmount, $honeyLayerAmount, $frameAmount, $count_start);
        
        // print_r($location);
        // die();
        
        return $this->show($request, $location);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Location $location)
    {
        return response()->json(['locations'=>[$request->user()->locations()->with('hives.layers.frames', 'hives.queen')->findOrFail($location->id)]]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $location                = $request->user()->locations()->findOrFail($id);
        // To do: edit continent and type
        $location->name          = $request->input('name'); 
        $location->coordinate_lat= $request->input('lat', 52);
        $location->coordinate_lon= $request->input('lon', 5);
        //$location->city          = $request->input('city');
        $location->street        = $request->input('street');
        $location->street_no     = $request->input('street_no');
        $location->postal_code   = $request->input('postal_code');
        $location->country_code  = $request->input('country_code', 'nl');

        $request->user()->locations()->save($location);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Location $location)
    {
        $location = $request->user()->locations()->findOrFail($location->id);
        $location->delete();
    }
}
