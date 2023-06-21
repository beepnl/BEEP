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

/**
 * @group Api\HiveController
 * Manage your hives
 * @authenticated
 */
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
        if ($request->filled('queen.race_id') || $request->filled('queen.name') || $request->filled('queen.birth_date') || $request->filled('queen.color') || $request->filled('queen.clipped') || $request->filled('queen.fertilized') || $request->filled('queen.description') || $request->filled('queen.line') || $request->filled('queen.tree'))
        {
            $race_id = Category::findCategoryIdByParentAndName('subspecies', 'other');
            $date  = $request->filled('queen.birth_date') ? date('Y-m-d', strtotime($request->input('queen.birth_date'))) : null;
            $queen = [
                    'name'          =>$request->input('queen.name'),
                    'description'   =>$request->input('queen.description'),
                    'line'          =>$request->input('queen.line'),
                    'tree'          =>$request->input('queen.tree'),
                    'race_id'       =>$request->input('queen.race_id', $race_id),
                    'birth_date'    =>$date,
                    'color'         =>$request->input('queen.color'),
                    'clipped'       =>boolval($request->input('queen.clipped')),
                    'fertilized'    =>boolval($request->input('queen.fertilized')),
                ];

            if ($request->filled('queen.id') && empty($queen['race_id'])) // update of queen without race defined, set to default
                $queen['race_id'] = $race_id;

            $hive->queen()->updateOrCreate(['id'=>$request->input('queen.id', null)], $queen);
        }
        return $hive;
    }

    /**
     * api/hives GET
     * Display a listing of user hives.
     * @return \Illuminate\Http\Response
     * @authenticated
     * @response{
    "hives": [
        {
            "id": 1,
            "location_id": 1,
            "hive_type_id": 43,
            "color": "#35f200",
            "name": "Kast 1",
            "created_at": "2017-07-13 23:34:49",
            "type": "spaarkast",
            "location": "",
            "attention": null,
            "impression": null,
            "reminder": null,
            "reminder_date": null,
            "inspection_count": 0,
            "sensors": [
                3,
                19
            ],
            "owner": true,
            "layers": [
                {
                    "id": 1,
                    "order": 0,
                    "color": "#35f200",
                    "type": "brood",
                    "framecount": 10
                },
                {
                    "id": 2,
                    "order": 1,
                    "color": "#35f200",
                    "type": "brood",
                    "framecount": 10
                },
                {
                    "id": 3,
                    "order": 2,
                    "color": "#35f200",
                    "type": "honey",
                    "framecount": 10
                }
            ],
            "queen": null
        }
    ]}
    */
    public function index(Request $request)
    {
        if ($request->user()->hives()->count() > 0)
            return response()->json(['hives'=>$request->user()->hives()->with('layers.frames', 'queen')->get()]);

        return response()->json(['error'=>'no hives available'],404);
    }

  
    /**
     * api/hives POST
     * Store a newly created Hive in storage for the authenticated user.
     * @authenticated
     * @param  \App\Requests\PostHiveRequest $request
     * @return \App\Hive
     */
    public function store(PostHiveRequest $request)
    {
        
        $user_id          = $request->user()->id;
        $location         = $request->user()->locations()->findOrFail($request->input('location_id'));
        $name             = $request->input('name'); 
        $order            = $request->input('order', null); 
        $bb_width_cm      = $request->input('bb_width_cm', null); 
        $bb_depth_cm      = $request->input('bb_depth_cm', null); 
        $bb_height_cm     = $request->input('bb_height_cm', null); 
        $fr_width_cm      = $request->input('fr_width_cm', null); 
        $fr_height_cm     = $request->input('fr_height_cm', null); 
        $hive_type_id     = $request->input('hive_type_id', 63); 
        $color            = $request->input('color', '#FABB13'); // yellow
        $broodLayerAmount = $request->input('brood_layers', 1);
        $honeyLayerAmount = $request->input('honey_layers', 1);
        $frameAmount      = $request->input('frames', 10);
        $layers           = $request->input('layers', null);
        $timeZone         = $request->input('timezone', 'Europe/Amsterdam');

        $hive = $this->hiveFactory->createHive($user_id, $location, $name, $hive_type_id, $color, $broodLayerAmount, $honeyLayerAmount, $frameAmount, $bb_width_cm, $bb_depth_cm, $bb_height_cm, $fr_width_cm, $fr_height_cm, $order, $layers);
        $hive = $this->saveQueen($request, $hive);

        return $this->show($request, $hive);
    }

    /**
     * api/hives/{id} GET
     * Display the specified resource.
     * @authenticated
     * @param  \App\Hive  $hive
     * @return \App\Hive
     */
    public function show(Request $request, Hive $hive)
    {
        return response()->json(['hives'=>[$request->user()->allhives()->orderBy('name')->with('layers.frames', 'queen')->findOrFail($hive->id)]]);
    }


    /**
     * api/hives/{id} PATCH
     * Update the specified user Hive in storage.
     * @authenticated
     * @param  \App\Requests\PostHiveRequest $request
     * @param  \App\Hive  $hive
     * @return \App\Hive
     */
    public function update(PostHiveRequest $request, Hive $hive)
    {
        $hive             = $request->user()->allhives(true)->findOrFail($hive->id);
        $location         = $request->user()->allLocations(true)->findOrFail($request->input('location_id'));
        $name             = $request->input('name');
        $order            = $request->input('order', null); 
        $bb_width_cm      = $request->input('bb_width_cm', null); 
        $bb_depth_cm      = $request->input('bb_depth_cm', null); 
        $bb_height_cm     = $request->input('bb_height_cm', null); 
        $fr_width_cm      = $request->input('fr_width_cm', null); 
        $fr_height_cm     = $request->input('fr_height_cm', null); 
        $hive_type_id     = $request->input('hive_type_id'); 
        $color            = $request->input('color', '#FABB13'); // yellow
        $broodLayerAmount = $request->input('brood_layers', 1);
        $honeyLayerAmount = $request->input('honey_layers', 1);
        $frameAmount      = $request->input('frames', 10);
        $layers           = $request->input('layers', null);
        $timeZone         = $request->input('timezone', 'Europe/Amsterdam');

        $hive = $this->hiveFactory->updateHive($hive, $location, $name, $hive_type_id, $color, $broodLayerAmount, $honeyLayerAmount, $frameAmount, $bb_width_cm, $bb_depth_cm, $bb_height_cm, $fr_width_cm, $fr_height_cm, $order, $layers, $timeZone);
        $hive = $this->saveQueen($request, $hive);

        return $this->show($request, $hive);
    }

    /**
     * api/hives/{id} DELETE
     * Remove the specified user Hive from storage.
     * @authenticated
     * @param  \App\Hive  $hive
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Hive $hive)
    {
        $hive = $request->user()->hives()->findOrFail($hive->id);
        $hive->delete();
        return response()->json(null, 204);
    }
}
