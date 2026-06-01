<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use App\Checklist;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Api\ChecklistController
 * Manage your personal inspection checklists
 *
 * @authenticated
 */
class ChecklistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $checklists = $request->user()->allChecklists()->orderBy('name')->get();

        return response()->json($checklists);
    }

    public function store(Request $request): JsonResponse
    {
        $requestData = $request->except(['user_id']);
        $checklist = Checklist::create($requestData);
        $request->user()->checklists()->attach($checklist);

        if ($request->filled('categories')) {
            $categories = explode(',', $request->input('categories'));
            $checklist->syncCategories($categories);
        }

        return response()->json(['checklist_id' => $checklist->id], 201);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $checklist = $request->user()->allChecklists()->find($id);
        if ($checklist) {
            $selected = $checklist->categoryIdArray();
            $checklist->taxonomy = $checklist->getOrderedChecklist($selected);

            return response()->json($checklist); // formatting for jsTree
        }

        return response()->json(null, 404);
    }

    public function update(Request $request, $id): JsonResponse
    {

        $requestData = $request->except(['user_id']);

        $checklist = $request->user()->checklists()->find($id);
        if ($checklist) {
            $checklist->update($requestData);

            if ($request->filled('categories')) {
                $categories = explode(',', $request->input('categories'));
                $checklist->syncCategories($categories);

                return response()->json(['checklist_id' => $checklist->id]);
            }
        }

        return response()->json('Nothing updated', 500);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        return response()->json($request->user()->checklists()->findOrFail($id)->delete());
    }
}
