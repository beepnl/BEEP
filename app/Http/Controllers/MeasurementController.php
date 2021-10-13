<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Measurement;
use Illuminate\Http\Request;
use Cache;

class MeasurementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $keyword = $request->get('search');

        if (!empty($keyword)) {
            $measurement = Measurement::where('abbreviation', 'LIKE', "%$keyword%")->get();
        } else {
            $measurement = Measurement::all();
        }

        return view('measurement.index', compact('measurement'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('measurement.create');
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
        $requestData['min_value'] = $request->filled('min_value') ? floatval($request->input('min_value')) : null;
        $requestData['max_value'] = $request->filled('max_value') ? floatval($request->input('max_value')) : null;

        Measurement::create($requestData);

        return redirect('measurement')->with('flash_message', 'Measurement added!');
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
        $measurement = Measurement::findOrFail($id);

        return view('measurement.show', compact('measurement'));
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
        $measurement = Measurement::findOrFail($id);

        return view('measurement.edit', compact('measurement'));
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
        $requestData['min_value'] = $request->filled('min_value') ? floatval($request->input('min_value')) : null;
        $requestData['max_value'] = $request->filled('max_value') ? floatval($request->input('max_value')) : null;
        
        $measurement = Measurement::findOrFail($id);
        $measurement->update($requestData);

        Cache::forget('taxonomy-lists');

        return redirect('measurement')->with('flash_message', 'Measurement updated!');
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
        Measurement::destroy($id);

        return redirect('measurement')->with('flash_message', 'Measurement deleted!');
    }
}
