<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\AlertRule;
use Illuminate\Http\Request;

class AlertRuleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user_id = $request->input('user_id');
        $rule_id  = $request->input('rule_id');
        $perPage = 100;

        if (!empty($user_id)) {
            $alertrule = AlertRule::where('user_id', $user_id)
                ->paginate($perPage);
        }
        else if (!empty($search)) {
            $alertrule = AlertRule::where('id', $rule_id)
                ->paginate($perPage);
        } else {
            $alertrule = AlertRule::paginate($perPage);
        }

        return view('alert-rule.index', compact('alertrule','rule_id','user_id'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $alertrule = new AlertRule();
        return view('alert-rule.create');
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
			'measurement_id' => 'required',
			'calculation' => 'required',
			'comparator' => 'required',
			'comparison' => 'required',
			'threshold_value' => 'required'
		]);
        $requestData = $request->all();

        if ($request->filled('exclude_hive_ids'))
            $requestData['exclude_hive_ids'] = implode(",", $requestData['exclude_hive_ids']);
        else
            $requestData['exclude_hive_ids'] = null;

        if ($request->filled('exclude_months'))
            $requestData['exclude_months'] = implode(",", $requestData['exclude_months']);
        else
            $requestData['exclude_months'] = null;

        if ($request->filled('exclude_hours'))
            $requestData['exclude_hours'] = implode(",", $requestData['exclude_hours']);
        else
            $requestData['exclude_hours'] = null;
        
        AlertRule::create($requestData);

        return redirect('alert-rule')->with('flash_message', 'AlertRule added!');
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
        $alertrule = AlertRule::findOrFail($id);

        return view('alert-rule.show', compact('alertrule'));
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
        $alertrule = AlertRule::findOrFail($id);

        return view('alert-rule.edit', compact('alertrule'));
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
			'measurement_id' => 'required',
			'calculation' => 'required',
			'comparator' => 'required',
			'comparison' => 'required',
			'threshold_value' => 'required'
		]);
        $requestData = $request->all();
        
        if ($request->filled('exclude_hive_ids'))
            $requestData['exclude_hive_ids'] = implode(",", $requestData['exclude_hive_ids']);
        else
            $requestData['exclude_hive_ids'] = null;

        if ($request->filled('exclude_months'))
            $requestData['exclude_months'] = implode(",", $requestData['exclude_months']);
        else
            $requestData['exclude_months'] = null;

        if ($request->filled('exclude_hours'))
            $requestData['exclude_hours'] = implode(",", $requestData['exclude_hours']);
        else
            $requestData['exclude_hours'] = null;

        $alertrule = AlertRule::findOrFail($id);
        $alertrule->update($requestData);

        return redirect('alert-rule')->with('flash_message', 'AlertRule updated!');
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
        AlertRule::destroy($id);

        return redirect('alert-rule')->with('flash_message', 'AlertRule deleted!');
    }
}
