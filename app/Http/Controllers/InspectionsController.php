<?php

namespace App\Http\Controllers;

use App\Inspection;
use Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InspectionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        // if (Auth::user()->hasRole('superadmin'))
        //     $inspections = Inspection::all();
        // else
        $inspections = $this->getUserInspections()->get();

        return view('inspections.index', compact('inspections'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('inspections.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request): RedirectResponse
    {

        $requestData = $request->all();

        $this->getUserInspections()->create($requestData);

        return redirect('inspections')->with('flash_message', 'Inspection added!');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View
    {
        if (Auth::user()->hasRole(['admin', 'superadmin'])) {
            $inspection = Inspection::find($id);
        } else {
            $inspection = $this->getUserInspections()->find($id);
        }

        if ($inspection) {
            $items = $inspection->items()->get();
        } else {
            return redirect('inspections')->with('error_message', "Inspection $id not found!");
        }
        // die(print_r($items->toArray()));

        return view('inspections.show', compact('inspection', 'items'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $inspection = $this->getUserInspections()->find($id);

        return view('inspections.edit', compact('inspection'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, int $id): RedirectResponse
    {

        $requestData = $request->all();

        $inspection = $this->getUserInspections()->find($id);
        $inspection->update($requestData);

        return redirect('inspections')->with('flash_message', 'Inspection updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->getUserInspections()->find($id)->delete();

        return redirect('inspections')->with('flash_message', 'Inspection deleted!');
    }

    private function getUserInspections()
    {
        // if (Auth::user()->hasRole('superadmin'))
        // {
        //     return Inspection::all();
        // }
        return Auth::user()->inspections();
    }
}
