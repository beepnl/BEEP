<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
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
        $rule_id   = $request->input('rule_id');
        $device_id = $request->input('device_id');
        $user_id   = $request->input('user_id');
        $no_meas   = boolval($request->input('no_measurements', 0));
        $users     = User::all()->pluck('name', 'id');
        $perPage = 100;

        if (!empty($rule_id))
        {
            $alert = Alert::where('alert_rule_id', $rule_id)
                ->paginate($perPage);
        } 
        else if (!empty($device_id))
        {
            $alert = Alert::where('device_id', $device_id)
                ->paginate($perPage);
        } 
        else if (!empty($user_id))
        {
            $alert = Alert::where('user_id', $user_id)
                ->orderByDesc('updated_at')
                ->paginate($perPage);
        }
        else if ($no_meas)
        {
            $alert = Alert::where('alert_value', '=', 0)->paginate($perPage);
        }
        else
        {
            $alert = Alert::orderByDesc('updated_at')->paginate($perPage);
        }

        return view('alert.index', compact('alert','rule_id','device_id','user_id','no_meas','users'));
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
