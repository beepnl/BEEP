<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Models\ChecklistSvg;
use Illuminate\Http\Request;

/**
 * @group Api\ChecklistSvgController
 * Manage stored SVG checklists (for off-line input)
 * @authenticated
 */
class ChecklistSvgController extends Controller
{
    /**
    api/checklist-svg GET
    Show your list of stored SVG inspections
    @authenticated
    **/
    public function index(Request $request)
    {
        $checklist_svg = $request->user()->checklistSvgs;

        return $checklist_svg;
    }

    /**
    api/checklist-svg POST
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
        
        $checklist_svg = $request->user()->checklistSvgs()->create($request->all());

        return response()->json($checklist_svg, 201);
    }

    /**
    api/checklist-svg/{id} GET
    Show an SVG inspection
    @authenticated
    **/
    public function show($id)
    {
        $checklist_svg = $request->user()->checklistSvgs()->findOrFail($id);

        return $checklist_svg;
    }

    /**
    api/checklist-svg/{id} PATCH
    Edit an SVG inspection
    @authenticated
    **/
    public function update(Request $request, $id)
    {
        
        $checklist_svg = $request->user()->checklistSvgs()->findOrFail($id);
        $checklist_svg->update($request->all());

        return response()->json($checklist_svg, 200);
    }

    /**
    api/checklist-svg/{id} DELETE
    Delete an SVG inspection
    @authenticated
    **/
    public function destroy($id)
    {
        $request->user()->checklistSvgs()->destroy($id);

        return response()->json(null, 204);
    }
}
