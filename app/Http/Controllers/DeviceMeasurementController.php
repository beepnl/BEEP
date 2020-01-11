<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\DeviceMeasurement;
use Illuminate\Http\Request;

class DeviceMeasurementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $keyword = $request->get('search');
        $perPage = 25;

        if (!empty($keyword)) {
            $devicemeasurement = DeviceMeasurement::where('zero_value', 'LIKE', "%$keyword%")
                ->orWhere('unit_per_value', 'LIKE', "%$keyword%")
                ->orWhere('measurement_id', 'LIKE', "%$keyword%")
                ->orWhere('physical_quantity_id', 'LIKE', "%$keyword%")
                ->orWhere('sensor_id', 'LIKE', "%$keyword%")
                ->paginate($perPage);
        } else {
            $devicemeasurement = DeviceMeasurement::paginate($perPage);
        }

        return view('device-measurement.index', compact('devicemeasurement'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('device-measurement.create');
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
        
        DeviceMeasurement::create($requestData);

        return redirect('device-measurement')->with('flash_message', 'DeviceMeasurement added!');
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
        $devicemeasurement = DeviceMeasurement::findOrFail($id);

        return view('device-measurement.show', compact('devicemeasurement'));
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
        $devicemeasurement = DeviceMeasurement::findOrFail($id);

        return view('device-measurement.edit', compact('devicemeasurement'));
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
        
        $devicemeasurement = DeviceMeasurement::findOrFail($id);
        $devicemeasurement->update($requestData);

        return redirect('device-measurement')->with('flash_message', 'DeviceMeasurement updated!');
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
        DeviceMeasurement::destroy($id);

        return redirect('device-measurement')->with('flash_message', 'DeviceMeasurement deleted!');
    }
}
