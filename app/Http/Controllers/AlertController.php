<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlertController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $rule_id = $request->input('rule_id');
        $device_id = $request->input('device_id');
        $user_id = $request->input('user_id');
        $no_meas = boolval($request->input('no_measurements', 0));
        $users = User::all()->pluck('name', 'id');
        $perPage = 100;

        if (! empty($rule_id)) {
            $alert = Alert::where('alert_rule_id', $rule_id)
                ->paginate($perPage);
        } elseif (! empty($device_id)) {
            $alert = Alert::where('device_id', $device_id)
                ->paginate($perPage);
        } elseif (! empty($user_id)) {
            $alert = Alert::where('user_id', $user_id)
                ->orderByDesc('updated_at')
                ->paginate($perPage);
        } elseif ($no_meas) {
            $alert = Alert::where('alert_value', '=', 0)->paginate($perPage);
        } else {
            $alert = Alert::orderByDesc('updated_at')->paginate($perPage);
        }

        return view('alert.index', compact('alert', 'rule_id', 'device_id', 'user_id', 'no_meas', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $alert = new Alert;

        return view('alert.create');
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
            'alert_rule_id' => 'required',
            'measurement_id' => 'required',
            'alert_value' => 'required',
            'user_id' => 'required',
        ]);
        $requestData = $request->all();

        Alert::create($requestData);

        return redirect('alert')->with('flash_message', 'Alert added!');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View
    {
        $alert = Alert::findOrFail($id);

        return view('alert.show', compact('alert'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $alert = Alert::findOrFail($id);

        return view('alert.edit', compact('alert'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $this->validate($request, [
            'alert_rule_id' => 'required',
            'measurement_id' => 'required',
            'alert_value' => 'required',
            'user_id' => 'required',
        ]);
        $requestData = $request->all();

        $alert = Alert::findOrFail($id);
        $alert->update($requestData);

        return redirect('alert')->with('flash_message', 'Alert updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(int $id): RedirectResponse
    {
        Alert::destroy($id);

        return redirect('alert')->with('flash_message', 'Alert deleted!');
    }
}
