<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Response;
use App\Group;
use App\Hive;

class GroupController extends Controller
{

    public function index(Request $request, $code=200)
    {
        $groups = $request->user()->groups()->orderBy('name')->get();
        return response()->json($groups, $code);
    }


    private function syncHives(Request $request, $group)
    {   
        // add edit_hive states to group_hive association
        $hive_ids = $request->input('hives_selected');
        $edit_ids = $request->input('hives_editable');
        $sync_ids = [];
        foreach ($hive_ids as $i => $hive_id) 
        {
            $sync_ids[$hive_id] = ['edit_hive'=>false];
            if (in_array($hive_id, $edit_ids))
                $sync_ids[$hive_id] = ['edit_hive'=>true];
        }
        return $group->hives()->sync($sync_ids);
    }


    public function store(Request $request)
    {
        $requestData = $request->only(['name','description','hex_color']);
        $requestData = 
        $group       = Group::create($requestData);
        $request->user()->groups()->attach($group, ['creator'=>true,'admin'=>true]);
        $this->syncHives($request, $group);
        return $this->index($request, 201);
    }


    public function show(Request $request, $id)
    {
        $group = $request->user()->groups()->find($id);
        if ($group)
        {
            return response()->json($group); // formatting for jsTree
        }
        return response()->json(null, 404);
    }


    public function update(Request $request, $id)
    {
        
        $requestData = $request->only(['id','name','description','hex_color']);
        $group = $request->user()->groups()->find($id);

        if ($group)
        {
            $group->update($requestData);
            $this->syncHives($request, $group);
            return $this->index($request, 200);
        }
        return Response::json('no_group_found', 404);
    }


    public function destroy(Request $request, $id)
    {
        $request->user()->groups()->findOrFail($id)->delete();
        
        return $this->index($request);
    }
    
    


}
