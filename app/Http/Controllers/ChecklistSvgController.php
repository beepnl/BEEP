<?php

namespace App\Http\Controllers;

use App\Models\ChecklistSvg;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChecklistSvgController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $keyword = $request->input('search');
        $perPage = 25;

        if (! empty($keyword)) {
            $checklistsvg = ChecklistSvg::where('user_id', 'LIKE', "%$keyword%")
                ->orWhere('checklist_id', 'LIKE', "%$keyword%")
                ->orWhere('name', 'LIKE', "%$keyword%")
                ->orWhere('svg', 'LIKE', "%$keyword%")
                ->orWhere('pages', 'LIKE', "%$keyword%")
                ->orWhere('last_print', 'LIKE', "%$keyword%")
                ->paginate($perPage);
        } else {
            $checklistsvg = ChecklistSvg::paginate($perPage);
        }

        return view('checklist-svg.index', compact('checklistsvg'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $checklistsvg = new ChecklistSvg;

        return view('checklist-svg.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => 'required',
            'checklist_id' => 'required',
        ]);
        $requestData = $request->all();

        ChecklistSvg::create($requestData);

        return redirect('checklist-svg')->with('flash_message', 'ChecklistSvg added!');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View
    {
        $checklistsvg = ChecklistSvg::findOrFail($id);

        return view('checklist-svg.show', compact('checklistsvg'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $checklistsvg = ChecklistSvg::findOrFail($id);

        return view('checklist-svg.edit', compact('checklistsvg'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'user_id' => 'required',
            'checklist_id' => 'required',
        ]);
        $requestData = $request->all();

        $checklistsvg = ChecklistSvg::findOrFail($id);
        $checklistsvg->update($requestData);

        return redirect('checklist-svg')->with('flash_message', 'ChecklistSvg updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(int $id): RedirectResponse
    {
        ChecklistSvg::destroy($id);

        return redirect('checklist-svg')->with('flash_message', 'ChecklistSvg deleted!');
    }
}
