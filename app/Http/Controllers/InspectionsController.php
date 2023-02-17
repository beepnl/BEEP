<?php

namespace App\Http\Controllers;

use Auth;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Inspection;
use Illuminate\Http\Request;

class InspectionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // if (Auth::user()->hasRole('superadmin'))
        //     $inspections = Inspection::all();
        // else
            $inspections = $this->getUserInspections()->get();

        return view('inspections.index', compact('inspections'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('inspections.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        
        $requestData = $request->all();
        
        $this->getUserInspections()->create($requestData);

        return redirect('inspections')->with('flash_message', 'Inspection added!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        if (Auth::user()->hasRole(['admin','superadmin']))
            $inspection = Inspection::find($id);
        else
            $inspection = $this->getUserInspections()->find($id);
        
        if ($inspection)
            $items = $inspection->items()->get();
        else
            return redirect('inspections')->with('error_message', "Inspection $id not found!");
        //die(print_r($items->toArray()));

        return view('inspections.show', compact('inspection','items'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $inspection = $this->getUserInspections()->find($id);

        return view('inspections.edit', compact('inspection'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, $id)
    {
        
        $requestData = $request->all();
        
        $inspection = $this->getUserInspections()->find($id);
        $inspection->update($requestData);

        return redirect('inspections')->with('flash_message', 'Inspection updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($id)
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
