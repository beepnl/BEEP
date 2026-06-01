<?php

namespace App\Http\Controllers;

use App\Models\CalculationModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalculationModelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $keyword = $request->get('search');
        $perPage = 25;

        if (! empty($keyword)) {
            $calculationmodel = CalculationModel::where('name', 'LIKE', "%$keyword%")
                ->orWhere('measurement_id', 'LIKE', "%$keyword%")
                ->orWhere('data_measurement_id', 'LIKE', "%$keyword%")
                ->orWhere('data_interval', 'LIKE', "%$keyword%")
                ->orWhere('data_relative_interval', 'LIKE', "%$keyword%")
                ->orWhere('data_interval_index', 'LIKE', "%$keyword%")
                ->orWhere('data_api_url', 'LIKE', "%$keyword%")
                ->orWhere('data_api_http_request', 'LIKE', "%$keyword%")
                ->orWhere('data_last_call', 'LIKE', "%$keyword%")
                ->orWhere('calculation', 'LIKE', "%$keyword%")
                ->orWhere('repository_url', 'LIKE', "%$keyword%")
                ->paginate($perPage);
        } else {
            $calculationmodel = CalculationModel::paginate($perPage);
        }

        return view('calculation-model.index', compact('calculationmodel'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $calculationmodel = new CalculationModel;

        return view('calculation-model.create');
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

        CalculationModel::create($requestData);

        return redirect('calculation-model')->with('flash_message', 'CalculationModel added!');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View
    {
        $calculationmodel = CalculationModel::findOrFail($id);

        return view('calculation-model.show', compact('calculationmodel'));
    }

    public function run(Request $request, $id): View
    {
        $calculationmodel = CalculationModel::findOrFail($id);
        $model_result = $calculationmodel->run_model($request->user());

        return view('calculation-model.show', compact('calculationmodel', 'model_result'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $calculationmodel = CalculationModel::findOrFail($id);

        return view('calculation-model.edit', compact('calculationmodel'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, int $id): RedirectResponse
    {

        $requestData = $request->all();

        $calculationmodel = CalculationModel::findOrFail($id);
        $calculationmodel->update($requestData);

        return redirect('calculation-model')->with('flash_message', 'CalculationModel updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(int $id): RedirectResponse
    {
        CalculationModel::destroy($id);

        return redirect('calculation-model')->with('flash_message', 'CalculationModel deleted!');
    }
}
