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
use Moment\Moment;
use Auth;
use LaravelLocalization;

/**
 * @group Api\InspectionsController
 */
class InspectionsController extends Controller
{
    
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
    @bodyParam id integer required The hive to request inspections from. 
    @response {
    "inspections": [
        {
            "id": 93,
            "notes": null,
            "reminder": null,
            "reminder_date": null,
            "impression": null,
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

        $inspections   = $hive->inspections_by_date();
        $items_by_date = $hive->inspection_items_by_date($locale);

        return response()->json(['inspections'=>$inspections, 'items_by_date'=>$items_by_date]);
    }

    public function show(Request $request, $id)
    {
        $inspection = $request->user()->allInspections()->find($id);
        if (isset($inspection) == false)
            return response()->json(null, 404);

        $inspection_items  = InspectionItem::where('inspection_id',$inspection->id)->groupBy('category_id')->get();
        $inspection->items = $inspection_items;
        return response()->json($inspection);
    }


    public function store(Request $request)
    {
        if ($request->filled('date') && ( $request->filled('items') || ($request->filled('item_ids') && $request->filled('item_vals')) ) )
        {
            $moment       = new Moment($request->input('date'));
            $date         = $moment->format('Y-m-d H:i:s');
            
            $data         = $request->except(['hive_id','items','date']);
            $user         = Auth::user();
            $hive         = $user->allHives(true)->find($request->input('hive_id'));
            $location     = $user->allLocations(true)->find($request->input('location_id'));

            $data['created_at']   = $date;
            $data['checklist_id'] = $request->filled('checklist_id') ? $request->input('checklist_id') : null;

            if ($request->filled('reminder_date'))
            {
                $reminder_moment = new Moment($request->input('reminder_date'));
                $data['reminder_date'] = $reminder_moment->format('Y-m-d H:i:s');
            }

            // select inspection if exists
            $inspection = null;
            if ($hive)
                $inspection = $hive->inspections()->orderBy('created_at','desc')->where('created_at', $date)->first();
            else if ($location)
                $inspection = $location->inspections()->orderBy('created_at','desc')->where('created_at', $date)->first();
            else
                $inspection = $user->inspections()->orderBy('created_at','desc')->where('created_at', $date)->first();

            // filter -1 values for impression and attention
            $data['impression']   = $request->filled('impression') && $request->input('impression') > -1 ? $request->input('impression') : null;
            $data['attention']    = $request->filled('attention')  && $request->input('attention')  > -1 ? $request->input('attention')  : null;

            //die(print_r(['data'=>$data,'inspection'=>$inspection,'user'=>$user->inspections()->get()->toArray()]));


            if (isset($inspection))
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
            //die(print_r($request->input('items')));
            // clear to remove items not in input
            $inspection->items()->forceDelete();
            // add items in input

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
            
            if (count($items) > 0)
            {
                foreach ($items as $cat_id => $value) 
                {
                    $category = Category::find($cat_id);
                    if (isset($category) && isset($value))
                    {
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

        if (isset($inspection))
            return response()->json($inspection->id, 201);

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
