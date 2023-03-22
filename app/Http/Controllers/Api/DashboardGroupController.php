<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Models\DashboardGroup;
use Illuminate\Http\Request;

class DashboardGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $dgroup = Models\DashboardGroup::paginate(25);

        return $dgroup;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $dgroup = Models\DashboardGroup::create($request->all());

        return response()->json($dgroup, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $dgroup = Models\DashboardGroup::findOrFail($id);

        return $dgroup;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        
        $dgroup = Models\DashboardGroup::findOrFail($id);
        $dgroup->update($request->all());

        return response()->json($dgroup, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Models\DashboardGroup::destroy($id);

        return response()->json(null, 204);
    }
}
