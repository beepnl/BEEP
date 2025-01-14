<?php

namespace App\Http\Controllers\Api;

use App\Location;
use App\Continent;
use App\Category;
use App\HiveFactory;
use Illuminate\Http\Request;
use App\Http\Requests\PostLocationRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

use Validator;

/**
 * @group Api\LocationController
 * Manage Apiaries
 * @authenticated
 */
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
        if ($request->filled('ids'))
        {
            if (gettype($request->input('ids')) == 'array')
                $location_ids = $request->input('ids');
            else
                $location_ids = explode(',', $request->input('ids'));
        }

        if ($request->filled('root') && $request->input('root'))
        {
            if (isset($location_ids))
                return response()->json(['locations'=>$request->user()->locations()->whereIn('id', $location_ids)->get()]);
            else
                return response()->json(['locations'=>$request->user()->locations()->get()]);
        }

        if (isset($location_ids) && gettype($location_ids) == 'array')
            $locations = $request->user()->locations()->whereIn('id', $location_ids)->with(['hives.layers', 'hives.queen'])->get();
        else
            $locations = $request->user()->allApiaries();

        return response()->json(['locations'=>$locations]); // removed with(['hives.layers.frames', 'hives.queen'])
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->only('name','hive_type_id'),
        [
            'name'          => 'required|string',
            'hive_type_id'  => 'nullable|integer|exists:categories,id',
        ]);

        if ($validator->fails())
            return response()->json(['errors'=>$validator->errors()], 422);
       
        $name             = $request->input('name'); 
        $prefix           = $request->filled('prefix') == false && isset($name)? $name : $request->input('prefix'); 
        $continent        = Continent::where('abbr', $request->input('continent','eu'))->first();
        $category         = Category::findCategoryByParentAndName('location_type', $request->input('location_type','fixed'))->first();
        $location         = new Location([
                'name'          =>$name, 
                'roofed'        =>$request->input('roofed'),
                'order'         =>$request->input('order', null),
                'continent_id'  =>$continent->id, 
                'category_id'   =>$category->id,
                'coordinate_lat'=>$request->filled('lat') ? round($request->input('lat'),3) : null,
                'coordinate_lon'=>$request->filled('lon') ? round($request->input('lon'),3) : null,
                'city'          =>$request->input('city'),
                'street'        =>$request->input('street'),
                'street_no'     =>$request->input('street_no'),
                'postal_code'   =>$request->input('postal_code'),
                'country_code'  =>$request->input('country_code', 'nl'),
                'hex_color'     =>$request->input('hex_color'),
            ]);

        //die(print_r($location));
        $request->user()->locations()->save($location);
        
        $user_id          = $request->user()->id;
        $amount           = $request->input('hive_amount', 1); 
        $count_start      = intval($request->input('offset', 1)); // 1
        $hive_type_id     = $request->input('hive_type_id', 63); // custom 
        $color            = $request->input('color', '#FABB13'); // yellow
        $broodLayerAmount = $request->input('brood_layers', 1);
        $honeyLayerAmount = $request->input('honey_layers', 1);
        $frameAmount      = $request->input('frames', 10);
        $bb_width_cm      = $request->input('bb_width_cm', null); 
        $bb_depth_cm      = $request->input('bb_depth_cm', null); 
        $bb_height_cm     = $request->input('bb_height_cm', null); 
        $fr_width_cm      = $request->input('fr_width_cm', null); 
        $fr_height_cm     = $request->input('fr_height_cm', null);
        $layers           = $request->input('layers', null);

        $hives = $this->hiveFactory->createMultipleHives($user_id, $amount, $location, $prefix, $hive_type_id, $color, $broodLayerAmount, $honeyLayerAmount, $frameAmount, $count_start, $bb_width_cm, $bb_depth_cm, $bb_height_cm, $fr_width_cm, $fr_height_cm, $layers);
        
        // print_r($location);
        // die();
        $request->user()->emptyCache();
        
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
        $location = $request->user()->locations()->with('hives.layers.frames', 'hives.queen')->findOrFail($location->id);
        return response()->json(['locations'=>[$location]]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function update(PostLocationRequest $request, $id)
    {
        $location                = $request->user()->locations()->findOrFail($id);
        // To do: edit continent and type
        $location->name          = $request->input('name'); 
        $location->roofed        = $request->input('roofed');
        $location->coordinate_lat= $request->filled('lat') ? round($request->input('lat'),3) : null;
        $location->coordinate_lon= $request->filled('lon') ? round($request->input('lon'),3) : null;
        $location->city          = $request->input('city');
        $location->street        = $request->input('street');
        $location->street_no     = $request->input('street_no');
        $location->postal_code   = $request->input('postal_code');
        $location->country_code  = $request->input('country_code', 'nl');
        $location->hex_color     = $request->input('hex_color');

        $request->user()->locations()->save($location);
        $request->user()->emptyCache();
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
        $request->user()->emptyCache();
    }
}
