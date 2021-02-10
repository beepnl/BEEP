<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Alert;
use Illuminate\Http\Request;

class AlertController extends Controller
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
            $alert = Alert::where('alert_rule_id', 'LIKE', "%$keyword%")
                ->orWhere('alert_function', 'LIKE', "%$keyword%")
                ->orWhere('alert_value', 'LIKE', "%$keyword%")
                ->orWhere('measurement_id', 'LIKE', "%$keyword%")
                ->orWhere('show', 'LIKE', "%$keyword%")
                ->orWhere('location_name', 'LIKE', "%$keyword%")
                ->orWhere('hive_name', 'LIKE', "%$keyword%")
                ->orWhere('device_name', 'LIKE', "%$keyword%")
                ->orWhere('location_id', 'LIKE', "%$keyword%")
                ->orWhere('hive_id', 'LIKE', "%$keyword%")
                ->orWhere('device_id', 'LIKE', "%$keyword%")
                ->orWhere('user_id', 'LIKE', "%$keyword%")
                ->paginate($perPage);
        } else {
            $alert = Alert::paginate($perPage);
        }

        return view('alert.index', compact('alert'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $alert = new Alert();
        return view('alert.create');
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
        $this->validate($request, [
			'alert_rule_id' => 'required',
			'measurement_id' => 'required',
			'alert_value' => 'required',
			'user_id' => 'required'
		]);
        $requestData = $request->all();
        
        Alert::create($requestData);

        return redirect('alert')->with('flash_message', 'Alert added!');
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
        $alert = Alert::findOrFail($id);

        return view('alert.show', compact('alert'));
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
        $alert = Alert::findOrFail($id);

        return view('alert.edit', compact('alert'));
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
        $this->validate($request, [
			'alert_rule_id' => 'required',
			'measurement_id' => 'required',
			'alert_value' => 'required',
			'user_id' => 'required'
		]);
        $requestData = $request->all();
        
        $alert = Alert::findOrFail($id);
        $alert->update($requestData);

        return redirect('alert')->with('flash_message', 'Alert updated!');
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
        Alert::destroy($id);

        return redirect('alert')->with('flash_message', 'Alert deleted!');
    }
}
