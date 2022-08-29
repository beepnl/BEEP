<?php

namespace App\Http\Controllers\Api;

use App\Queen;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * @group Api\QueenController
 * Not used
 * @authenticated
 */
class QueenController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->user()->queens()->count() > 0)
            return response()->json(['queens'=>$request->user()->queens()->get()]);

        return response()->json(['error'=>'no queens available'],404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $race_id = $request->filled('race_id') ? $request->input('race_id') : Category::findCategoryIdByParentAndName('subspecies', 'other');
        $date    = $request->filled('birth_date') ? date('Y-m-d', strtotime($request->input('birth_date'))) : null;
        $queen   = [
            'name'          =>$request->input('name'),
            'description'   =>$request->input('description'),
            'line'          =>$request->input('line'),
            'tree'          =>$request->input('tree'),
            'hive_id'       =>$request->input('hive_id'),
            'race_id'       =>$race_id,
            'birth_date'    =>$date,
            'color'         =>$request->input('color'),
            'clipped'       =>boolval($request->input('clipped')),
            'fertilized'    =>boolval($request->input('fertilized')),
        ];

        return response()->json($request->user()->queens()->updateOrCreate(['queens.id'=>$request->input('id', null)], $queen));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Queen  $queen
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Queen $queen)
    {
        return response()->json($request->user()->queens()->findorFail($queen->id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Queen  $queen
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Queen $queen)
    {
        return $this->store($request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Queen  $queen
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Queen $queen)
    {
        $queen = $request->user()->queens()->findorFail($queen->id);
        $queen->delete();
        return response()->json(null, 204);
    }
}
