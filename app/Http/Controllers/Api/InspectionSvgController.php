<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Models\InspectionSvg;
use Illuminate\Http\Request;

/**
 * @group Api\InspectionSvgController
 * Manage stored SVG inspections (for off-line input)
 * @authenticated
 */
class InspectionSvgController extends Controller
{
    /**
    api/inspection-svg GET
    Show your list of stored SVG inspections
    @authenticated
    **/
    public function index(Request $request)
    {
        $inspection_svg = $request->user()->inspectionSvgs()->all();

        return $inspection_svg;
    }

    /**
    api/inspection-svg POST
    Store an SVG inspection
    @bodyParam checklist_id integer required The checklist ID that this SVG refers to (at the moment of storage)
    @bodyParam svg string required The SVG body to store (max 16,777,215 characters)
    @bodyParam pages integer The amount of pages of the SVG
    @bodyParam name string The name of the inspection SVG
    @bodyParam last_print datetime The last print datetime
    @authenticated
    **/
    public function store(Request $request)
    {
        
        $inspection_svg = $request->user()->inspectionSvgs()->create($request->all());

        return response()->json($inspection_svg, 201);
    }

    /**
    api/inspection-svg/{id} GET
    Show an SVG inspection
    @authenticated
    **/
    public function show($id)
    {
        $inspection_svg = $request->user()->inspectionSvgs()->findOrFail($id);

        return $inspection_svg;
    }

    /**
    api/inspection-svg/{id} PATCH
    Edit an SVG inspection
    @authenticated
    **/
    public function update(Request $request, $id)
    {
        
        $inspection_svg = $request->user()->inspectionSvgs()->findOrFail($id);
        $inspection_svg->update($request->all());

        return response()->json($inspection_svg, 200);
    }

    /**
    api/inspection-svg/{id} DELETE
    Delete an SVG inspection
    @authenticated
    **/
    public function destroy($id)
    {
        $request->user()->inspectionSvgs()->destroy($id);

        return response()->json(null, 204);
    }
}
