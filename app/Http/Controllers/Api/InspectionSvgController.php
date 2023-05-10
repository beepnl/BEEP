<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Models\InspectionSvg;
use Illuminate\Http\Request;

class InspectionSvgController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $inspection_svg = $request->user()->inspectionSvgs()->all();

        return $inspection_svg;
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
        
        $inspection_svg = $request->user()->inspectionSvgs()->create($request->all());

        return response()->json($inspection_svg, 201);
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
        $inspection_svg = $request->user()->inspectionSvgs()->findOrFail($id);

        return $inspection_svg;
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
        
        $inspection_svg = $request->user()->inspectionSvgs()->findOrFail($id);
        $inspection_svg->update($request->all());

        return response()->json($inspection_svg, 200);
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
        $request->user()->inspectionSvgs()->destroy($id);

        return response()->json(null, 204);
    }
}
