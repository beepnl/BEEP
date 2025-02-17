<?php

namespace App\Http\Controllers\Api;

use App\Category;
use App\Hive;
use App\BeeRace;
use App\Inspection;
use App\InspectionItem;
use App\Image;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\InspectionCollection;
use Moment\Moment;
use Auth;
use LaravelLocalization;
use Validator;

/**
 * @group Api\InspectionsController
 * Manage manual hive inspections
 * @authenticated
 */
class InspectionsController extends Controller
{
    /**
    api/inspections GET
    Show the 'inspections' list with objects reflecting only the general inspection data.
    @bodyParam hive_ids array Only show inspections connected to hive_ids.
    @bodyParam search string Filter on text inside notes, reminder, created_at, reminder_date, inspection item names/values. Example: test
    @bodyParam id integer Filter on one specific inspection id if filled. Example: 23
    @bodyParam start string Date >= (YYYY-MM-DD HH:mm:ss) to filter inspections from. Example: 2024-02-14 00:00:00
    @bodyParam end string Date <= (YYYY-MM-DD HH:mm:ss) to filter inspections to. Example: 2024-02-18 00:00:00
    @authenticated
    **/
    public function index(Request $request)
    {
        $inspections = $request->user()->allInspections()->orderBy('created_at', 'desc')->limit(1000);
        
        if ($request->filled('id'))
        {
            $id = $request->input('id');
            $inspections = $inspections->where('id', $id);
        }
        else if ($request->filled('search'))
        {
            $search = $request->input('search');
            $inspections = $inspections->where('note', 'LIKE', '%'.$search.'%')
                                        ->orWhere('created_at', 'LIKE', '%'.$search.'%')
                                        ->orWhere('reminder', 'LIKE', '%'.$search.'%')
                                        ->orWhere('id', intval($search));

            if ($request->filled('start'))
                $inspections = $inspections->where('created_at', '>=', $request->input('start'));

            if ($request->filled('end'))
                $inspections = $inspections->where('created_at', '<=', $request->input('end'));
        }
        
        $inspections = $inspections->get(); // convert query to collection

        // Add filters on items with appends
        if ($request->filled('hive_ids')) {
            $hive_ids_in = $request->input('hive_ids');
            $hive_ids    = gettype($hive_ids_in) == 'array' ? $hive_ids_in : explode(',', $hive_ids_in);
            // filter by occurence in hive_ids array 
            $inspections = $inspections->reject(function (Inspection $ins, int $key) use ($hive_ids){
                return in_array($ins->hive_id, $hive_ids) === false;
            });
        }

        if ($request->filled('location_ids')) {
            $location_ids_in = $request->input('location_ids');
            $location_ids    = gettype($location_ids_in) == 'array' ? $location_ids_in : explode(',', $location_ids_in);
            // filter by occurence in location_ids array 
            $inspections = $inspections->reject(function (Inspection $ins, int $key) use ($location_ids){
                return in_array($ins->location_id, $location_ids) === false;
            });
        }

        if (empty($inspections)) {
            return response()->json(null, 404);
        }

        if ($inspections->count() == 0)
            return response()->json(null, 404);

        return response()->json($inspections);
    }


    /**
    api/inspections/lists GET
    List checklists and its  inspections linked to Hive id. The 'inspections' object contains a descending date ordered list of general inspection data. The 'items_by_date' object contains a list of (rows of) inspection items that can be placed (in columns) under the inspections by created_at date (table format). NB: Use 'Accept-Language' Header (default nl_NL) to provide localized category names (anc, name) in items_by_date. 
    @authenticated
    @bodyParam id integer required The hive to request inspections from. 
    @response {
    "checklists": [
        {
            "id": 810,
            "type": "beep_v2_copy",
            "name": "Beep v2 - info@beep.nl",
            "description": null,
            "created_at": "2020-01-13 18:30:02",
            "updated_at": "2020-01-13 19:58:47",
            "category_ids": [
                149,
                771,
                963,
                964,
                965,
                966,
                263,
                265,
                270,
                276
            ],
            "required_ids": [],
            "owner": true,
            "researches": []
        }
    ]
}
    **/
    public function lists(Request $request)
    {
        $out               = [];
        $checklists        = $request->user()->allChecklists();
        $out['checklists'] = $checklists->orderBy('name')->get();
        
        $checklist    = null;

        if ($checklists->where('id',intval($request->input('id')))->count() > 0)
            $checklist = $checklists->where('id',intval($request->input('id')))->first();
        else
            $checklist = $request->user()->allChecklists()->orderBy('created_at', 'desc')->first();
    
        if ($checklist && $checklist->categories()->count() > 0)
            $checklist->categories = $checklist->categories()->get()->toTree();

        $out['checklist']  = $checklist;

        return response()->json($out);
    }

    /**
    api/inspections/hive/{hive_id} GET
    List all inspections linked to Hive id. The 'inspections' object contains a descending date ordered list of general inspection data. The 'items_by_date' object contains a list of (rows of) inspection items that can be placed (in columns) under the inspections by created_at date (table format). NB: Use 'Accept-Language' Header (default nl_NL) to provide localized category names (anc, name) in items_by_date. 
    @authenticated
    @urlParam hive_id required The hive to request inspections from. 
    @bodyParam search string Filter inspections on text inside notes, reminder, created_at, reminder_date, inspection item names/values. Example: test
    bodyParam id integer If provided, select single inspection. Example: 15
    @bodyParam impression string Filter by one or more impression values 1-3 (smileys). Default: null. Example: 2,3
    @bodyParam attention boolean Filter by having attention set (0-1). Default: null. Example: null
    @bodyParam reminder boolean Filter by having a reminder set (0-1). Default: null. Example: This is an inspection reminder
    @bodyParam start string Date >= (YYYY-MM-DD HH:mm:ss) to filter inspections from. Default: null. Example: 2024-02-14 00:00:00
    @bodyParam end string Date <= (YYYY-MM-DD HH:mm:ss) to filter inspections to. Default: null. Example: 2024-02-18 00:00:00
    @response {
    "inspections": [
        {
            "id": 93,
            "notes": null,
            "reminder": null,
            "reminder_date": null,
            "impression": 1,
            "attention": null,
            "created_at": "2020-05-18 12:34:00",
            "checklist_id": 829,
            "image_id": null,
            "owner": true,
            "thumb_url": null,
            "hive_id": 42
        },
        {
            "id": 91,
            "notes": null,
            "reminder": null,
            "reminder_date": null,
            "impression": 3,
            "attention": 0,
            "created_at": "2020-05-18 11:43:00",
            "checklist_id": 829,
            "image_id": null,
            "owner": true,
            "thumb_url": null,
            "hive_id": 42
        }
    ],
    "items_by_date": [
        {
            "anc": null,
            "name": "Bee colony",
            "items": null
        },
        {
            "anc": "Bee colony > Brood > ",
            "name": "Pattern consistency",
            "type": "score",
            "range": "min: 1 - max: 5",
            "items": [
                {
                    "id": 138,
                    "value": "3",
                    "inspection_id": 93,
                    "category_id": 279,
                    "val": "3",
                    "unit": null,
                    "type": "score"
                },
                ""
            ]
        },
        {
            "anc": "Bee colony > Brood > Status > ",
            "name": "All stages",
            "type": "boolean",
            "range": null,
            "items": [
                "",
                {
                    "id": 77,
                    "value": "1",
                    "inspection_id": 91,
                    "category_id": 868,
                    "val": "Yes",
                    "unit": null,
                    "type": "boolean"
                }
            ]
        },
        {
            "anc": "Bee colony > Brood > Status > ",
            "name": "Eggs",
            "type": "boolean",
            "range": null,
            "items": [
                "",
                {
                    "id": 308,
                    "value": "1",
                    "inspection_id": 91,
                    "category_id": 270,
                    "val": "Yes",
                    "unit": null,
                    "type": "boolean"
                }
            ]
        }
    ]
}
    */
    public function hive(Request $request, $hive_id)
    {
        $hive   = $request->user()->allHives()->findOrFail($hive_id);
        $locale = $request->filled('locale') ? $request->input('locale') : LaravelLocalization::getCurrentLocale();
        return response()->json($hive->inspection_items_by_date($request, $locale));
    }

    /**
    api/inspections/{id} GET
    Show the 'inspection' object. The object reflects only the general inspection data.
    @authenticated
    @urlParam id required The id of the inspection. 
    **/
    public function show(Request $request, $id)
    {
        $inspection = $request->user()->allInspections()->find($id);
        if (isset($inspection) == false)
            return response()->json(null, 404);

        $inspection->makeVisible('items');
        $inspection_items  = InspectionItem::where('inspection_id',$inspection->id)->groupBy('category_id')->get();
        $inspection->items = $inspection_items;
        return response()->json($inspection);
    }

    /**
    api/inspections POST
    Register a new hive inspection the 'inspection' object. The object reflects only the general inspection data.
    @authenticated
    @bodyParam date date required The (local time) date time of the inspection. Example: 2020-05-18 16:16
    @bodyParam items object required An object of category id's containing their inspected values (id's in case of lists, otherwise numeric/textual values). Example: {"547":0,"595":1,"845":"814"}
    @bodyParam hive_ids array required Array of Hive ids to which this inspection should be linked. Example: 42
    @bodyParam location_id Location id to which this inspection should be linked. Example: 2
    @bodyParam id integer If provided, edit and do not create inspection. Required to edit the inspection. Example: 15
    @bodyParam impression integer Numeric impression value -1 (unfilled) to 1-3 (smileys). Example: -1
    @bodyParam attention integer Numeric impression value -1 (unfilled) to 0-1 (needs attention). Example: 1
    @bodyParam reminder string Textual value of the reminder fields. Example: This is an inspection reminder
    @bodyParam reminder_date date The (local time) date time for an optional reminder that can be fed to the users calender. Example: 2020-05-27 16:16
    @bodyParam notes string Textual value of the notes fields. Example: This is an inspection note
    @bodyParam checklist_id integer Id of the user checklist for generating this inspection. Example: 829
    **/
    public function store(Request $request)
    {
        $validator = Validator::make($request->input(),
        [
            'date'          => 'required|date',
            'items'         => 'nullable',
            'hive_ids.*'    => 'required_without:hive_id|integer|exists:hives,id',
            'hive_id'       => 'required_without:hive_ids|integer|exists:hives,id',
        ]);

        if ($validator->fails())
            return response()->json(['errors'=>$validator->errors()], 422);

        $user = Auth::user();
        if ($request->filled('date') && ( $request->filled('items') || ($request->filled('item_ids') && $request->filled('item_vals')) ) )
        {
            $moment               = new Moment($request->input('date'));
            $date                 = $moment->format('Y-m-d H:i:s');
            $data                 = $request->except(['hive_id','items','date']);
            $data['created_at']   = $date;
            $data['checklist_id'] = $request->filled('checklist_id') ? $request->input('checklist_id') : null;

            if ($request->filled('reminder_date'))
            {
                $reminder_moment = new Moment($request->input('reminder_date'));
                $data['reminder_date'] = $reminder_moment->format('Y-m-d H:i:s');
            }
            
            // filter -1 values for impression and attention
            $data['impression']   = $request->filled('impression') && $request->input('impression') > -1 ? $request->input('impression') : null;
            $data['attention']    = $request->filled('attention')  && $request->input('attention')  > -1 ? $request->input('attention')  : null;

            // combine item_ids and item_vals to items
            if (!$request->filled('items') && $request->filled('item_ids') && $request->filled('item_vals'))
            {
                $items = [];
                $item_ids  = explode(',', $request->input('item_ids'));
                $item_vals = explode(',', $request->input('item_vals'));
                if (count($item_ids) == count($item_vals))
                {
                    for ($i=0; $i < count($item_ids); $i++) 
                    { 
                        $items[$item_ids[$i]] = $item_vals[$i];
                    }
                }
            }
            else
            {
                $items = $request->input('items');  
            }

            $location     = $user->allLocations(true)->find($request->input('location_id'));

            $hive_ids     = [];
            if ($request->filled('hive_ids'))
                $hive_ids = $request->input('hive_ids');
            else
                $hive_ids = [$request->input('hive_id')];


            $inspection = $user->inspections()->find($request->input('id'));

            foreach ($hive_ids as $hive_id) 
            {
                $hive = $user->allHives(true)->find($hive_id);

                if (!isset($hive))
                    continue;
                
                // if (!isset($inspection)) // if no inspection id 
                // {
                //     if ($hive)
                //         $inspection = $hive->inspections()->orderBy('created_at','desc')->where('created_at', $date)->first();
                //     else if ($location)
                //         $inspection = $location->inspections()->orderBy('created_at','desc')->where('created_at', $date)->first();
                //     else
                //         return response()->json('no_owner_or_edit_rights', 400);
                //         //$inspection = $user->inspections()->orderBy('created_at','desc')->where('created_at', $date)->first();
                // }

                if (isset($inspection) && $inspection->hive_id == $hive_id)
                    $inspection->update($data);
                else
                    $inspection = Inspection::create($data);
                
                // link inspection
                $inspection->users()->syncWithoutDetaching($user->id);

                if (isset($location))
                    $inspection->locations()->syncWithoutDetaching($location->id);

                if (isset($hive))
                    $inspection->hives()->syncWithoutDetaching($hive->id);


                // Set inspection items
                // clear to remove items not in input
                $inspection->items()->forceDelete();
                
                if (count($items) > 0)
                {
                    foreach ($items as $cat_id => $value) 
                    {
                        $category = Category::find($cat_id);
                        if (isset($category) && isset($value))
                        {
                            if (is_array($value))
                                $value = implode(',',$value); // convert value to string

                            $itemData = 
                            [
                                'category_id'   => $category->id,
                                'inspection_id' => $inspection->id,
                                'value'         => $value,
                            ];
                            InspectionItem::create($itemData);

                            // add inspection link to Image
                            if ($category->inputTypeType() == 'image')
                            {
                                $image = Image::where('thumb_url', $value)->first();
                                if ($image)
                                {
                                    $image->inspection_id = $inspection->id;
                                    $image->save();
                                }
                            }
                        }
                    }
                }
            }
        }

        if (isset($inspection))
        {
            // Empty user cache, because the inspection if synced 
            $user->emptyCache('inspection');
            return response()->json($inspection->id, 201);
        }

        return response()->json('error', 500);

    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Auth::user()->inspections()->findOrFail($id)->delete();
    }

}
