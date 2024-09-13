<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\User;
use App\Models\AlertRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Cache;

class AlertRuleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $perPage = 100;
        $user_id = $request->input('user_id');
        $rule_id = $request->input('rule_id');
        $users   = User::all()->pluck('name', 'id');
        $no_meas = boolval($request->input('no_measurements', 0));
        $default_rule = boolval($request->input('default_rule', 0));

        if (!empty($user_id)) {
            $alertrule = AlertRule::where('user_id', $user_id)
                ->paginate($perPage);
        }
        else if (!empty($rule_id)) 
        {
            $alertrule = AlertRule::where('id', $rule_id)
                ->paginate($perPage);
        } 
        else if ($default_rule)
        {
            $alertrule = AlertRule::where('default_rule', 1)->get()->paginate($perPage);
        }
        else if ($no_meas)
        {
            $alertrule = AlertRule::where('calculation_minutes', '>', 0)->get()->where('no_value', '=', 1)->paginate($perPage);
        }
        else 
        {
            $alertrule = AlertRule::paginate($perPage);
        }



        return view('alert-rule.index', compact('alertrule','rule_id','user_id','users','no_meas','default_rule'));
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
     * Parse AR
     */
    public function parse(Request $request, $id)
    {
        $alertrule = AlertRule::findOrFail($id);
        $alertrule->last_evaluated_at = null;
        $alertrule->last_calculated_at = null;

        $log_on = env('LOG_ALERT_RULE_PARSING', false);
        
        if($log_on)
            Log::info("Manual trigger of AlertRule $id via ->parseRule(null, null, $log_on)");

        $alerts = $alertrule->parseRule(null, null, $log_on);

        return redirect('alert-rule')->with('success', "Alert Rule ($id) $alertrule->name parsed. Result: ".json_encode($alerts));
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

        if (boolval($requestData['default_rule']))
            Cache::forget('alert-rules-default');

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

        if (boolval($requestData['default_rule']))
            Cache::forget('alert-rules-default');

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
