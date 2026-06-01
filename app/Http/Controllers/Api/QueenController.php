<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Queen;
use Illuminate\Http\Request;

/**
 * @group Api\QueenController
 * Not used
 *
 * @authenticated
 */
class QueenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        if ($request->user()->queens()->count() > 0) {
            return response()->json(['queens' => $request->user()->queens()->get()]);
        }

        return response()->json(['error' => 'no queens available'], 404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $race_id = $request->filled('race_id') ? $request->input('race_id') : Category::findCategoryIdByParentAndName('subspecies', 'other');
        $date = $request->filled('birth_date') ? date('Y-m-d', strtotime($request->input('birth_date'))) : null;
        $queen = [
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'line' => $request->input('line'),
            'tree' => $request->input('tree'),
            'hive_id' => $request->input('hive_id'),
            'race_id' => $race_id,
            'birth_date' => $date,
            'color' => $request->input('color'),
            'clipped' => boolval($request->input('clipped')),
            'fertilized' => boolval($request->input('fertilized')),
        ];

        return response()->json($request->user()->queens()->updateOrCreate(['queens.id' => $request->input('id', null)], $queen));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Queen $queen): JsonResponse
    {
        return response()->json($request->user()->queens()->findorFail($queen->id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Queen $queen)
    {
        return $this->store($request);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Queen $queen): JsonResponse
    {
        $queen = $request->user()->queens()->findorFail($queen->id);
        $queen->delete();

        return response()->json(null, 204);
    }
}
