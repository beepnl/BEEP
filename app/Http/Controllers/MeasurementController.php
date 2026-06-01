<?php

namespace App\Http\Controllers;

use App\Measurement;
use Cache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MeasurementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $keyword = $request->get('search');

        if (! empty($keyword)) {
            $measurement = Measurement::where('abbreviation', 'LIKE', "%$keyword%")->get();
        } else {
            $measurement = Measurement::all();
        }

        return view('measurement.index', compact('measurement'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('measurement.create');
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
        $requestData['min_value'] = $request->filled('min_value') ? floatval($request->input('min_value')) : null;
        $requestData['max_value'] = $request->filled('max_value') ? floatval($request->input('max_value')) : null;

        Measurement::create($requestData);

        return redirect('measurement')->with('flash_message', 'Measurement added!');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View
    {
        $measurement = Measurement::findOrFail($id);

        return view('measurement.show', compact('measurement'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $measurement = Measurement::findOrFail($id);

        return view('measurement.edit', compact('measurement'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, int $id): RedirectResponse
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
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(int $id): RedirectResponse
    {
        Measurement::destroy($id);

        return redirect('measurement')->with('flash_message', 'Measurement deleted!');
    }
}
