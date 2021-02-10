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
        $keyword = $request->get('search');
        $perPage = 25;

        if (!empty($keyword)) {
            $alertrule = AlertRule::where('name', 'LIKE', "%$keyword%")
                ->orWhere('description', 'LIKE', "%$keyword%")
                ->orWhere('measurement_id', 'LIKE', "%$keyword%")
                ->orWhere('calculation', 'LIKE', "%$keyword%")
                ->orWhere('calculation_minutes', 'LIKE', "%$keyword%")
                ->orWhere('comparator', 'LIKE', "%$keyword%")
                ->orWhere('comparison', 'LIKE', "%$keyword%")
                ->orWhere('threshold_value', 'LIKE', "%$keyword%")
                ->orWhere('exclude_months', 'LIKE', "%$keyword%")
                ->orWhere('exclude_hours', 'LIKE', "%$keyword%")
                ->orWhere('alert_via_email', 'LIKE', "%$keyword%")
                ->orWhere('webhook_url', 'LIKE', "%$keyword%")
                ->orWhere('active', 'LIKE', "%$keyword%")
                ->orWhere('user_id', 'LIKE', "%$keyword%")
                ->orWhere('default_rule', 'LIKE', "%$keyword%")
                ->paginate($perPage);
        } else {
            $alertrule = AlertRule::paginate($perPage);
        }

        return view('alert-rule.index', compact('alertrule'));
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
