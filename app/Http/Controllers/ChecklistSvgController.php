<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\ChecklistSvg;
use Illuminate\Http\Request;

class ChecklistSvgController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View
    {
        $keyword = $request->get('search');
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
     *
     * @return \Illuminate\View\View
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
        $this->validate($request, [
            'user_id' => 'required',
            'checklist_id' => 'required',
        ]);
        $requestData = $request->all();

        ChecklistSvg::create($requestData);

        return redirect('checklist-svg')->with('flash_message', 'ChecklistSvg added!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show(int $id): View
    {
        $checklistsvg = ChecklistSvg::findOrFail($id);

        return view('checklist-svg.show', compact('checklistsvg'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit(int $id): View
    {
        $checklistsvg = ChecklistSvg::findOrFail($id);

        return view('checklist-svg.edit', compact('checklistsvg'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $this->validate($request, [
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
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(int $id): RedirectResponse
    {
        ChecklistSvg::destroy($id);

        return redirect('checklist-svg')->with('flash_message', 'ChecklistSvg deleted!');
    }
}
