<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\AlertRule;
use App\Models\AlertRuleFormula;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use Auth;
use Validator;

/**
 * @group Api\AlertRuleController
 * Manage your alert rules
 * @authenticated
 */
class AlertRuleController extends Controller
{
    /**
     * api/alert-rules GET
     * List all user alert rules that are not deleted.
     * @authenticated
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->user()->alert_rules()->count() > 0)
            return response()->json(['alert_rules'=>$request->user()->alert_rules()->get()]);

        return response()->json(['error'=>'no alert_rules available'],404);
    }

    /**
     * api/alert-rules-default GET
     * List all default alert rules that are available.
     * @authenticated
     * @return \Illuminate\Http\Response
     */
    public function default(Request $request)
    {
        $alert_rules = AlertRule::where('default_rule', 1)->orderBy('name');

        if ($alert_rules->count() > 0)
            return response()->json(['alert-rules'=>$alert_rules->get()]);

        return response()->json(['error'=>'no default alert rules available'],404);
    }

    /**
     * api/alert-rules/{id} POST
     * Create the specified user alert rule.
     * @authenticated
     * @bodyParam measurement_id integer required The physical quantity / unit to alert for. 
     * @bodyParam calculation string required Calculation to be done with measurement value(s): (min, max, ave, der, cnt) -> Minimum, Maximum, Average (mean), Derivative, Count.
     * @bodyParam comparator string required Logical comparator to perform with comparison calculation result and threshold_value (=, <, >, <=, >=).
     * @bodyParam comparison string required Comparison function to perform with measurement value(s): (val, dif, abs, abs_dif) -> Value, Difference, Absolute value, Absolute value of the difference.
     * @bodyParam threshold_value float required The threshold value beyond which the alert will be sent. 
     * @bodyParam name string The name of the alert rule. 
     * @bodyParam description string The description of the alert rule. 
     * @bodyParam calculation_minutes integer The amount of minutes used for calculating the (min, max, ave, der, cnt) of the measurement value(s). If not provided, the last recorded value is used as a reference.
     * @bodyParam exclude_months array Array of month indexes (1-12). If not filled the standard alert is 'always on'. Example: [1,2,3,11,12]
     * @bodyParam exclude_hours array Array of hour indexes (0-23). If not filled the standard alert is 'always on'. Example: [0,1,2,3,22,23]
     * @bodyParam exclude_hive_ids array Array of Hive ids. If not filled the standard alert is evaluated on 'all hives'.
     * @bodyParam alert_on_occurrences integer Amount of occurences that a calculated value goed beyond the threshold_value. If not filled the standard is 1 (immediate alert).
     * @bodyParam alert_via_email boolean Set to false (0) if an e-mail should NOT be sent on alert. Default: true (1).
     * @bodyParam webhook_url string URL of optional endpoint to call on alert for web hook integration.
     * @bodyParam active boolean Set to false (0) if the alert should NOT be active. Default: true (1).
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'                      => 'nullable|string',
            'description'               => 'nullable|string',
            'formulas.*.alert_rule_id'  => 'nullable|integer|exists:alert_rules,id',
            'formulas.*.measurement_id' => 'required|integer|exists:measurements,id',
            'formulas.*.calculation'    => ['required', Rule::in(array_keys(AlertRuleFormula::$calculations))],
            'formulas.*.comparator'     => ['required', Rule::in(array_keys(AlertRuleFormula::$comparators))],
            'formulas.*.comparison'     => ['required', Rule::in(array_keys(AlertRuleFormula::$comparisons))],
            'formulas.*.period_minutes' => 'required|integer|min:0',
            'formulas.*.threshold_value'=> 'required|numeric',
            'formulas.*.future'         => 'required|boolean',
            'formulas.*.logical'        => ['nullable', Rule::in(array_keys(AlertRuleFormula::$logicals))],
            'calculation_minutes'       => ['required', Rule::in(AlertRule::$calc_minutes)],
            'exclude_months.*'          => ['nullable', 'integer', Rule::in(array_keys(AlertRule::$exclude_months))],
            'exclude_hours.*'           => ['nullable', 'integer', Rule::in(array_keys(AlertRule::$exclude_hours))],
            'exclude_hive_ids.*'        => ['nullable', 'integer'/*, Rule::in($request->user()->allHives()->pluck('id'))*/],
            'alert_on_occurrences'      => 'nullable|integer',
            'alert_via_email'           => 'nullable|boolean',
            'webhook_url'               => 'nullable|url',
            'active'                    => 'nullable|boolean',
        ]);

        if ($validator->fails())
            return response()->json(['errors'=>$validator->errors()]);

        $alert_rule_formula = AlertRuleFormula::create($request->all());

        if ($validator->fails())
            return response()->json(['errors'=>$validator->errors()]);


        $requestData = $request->except('default_rule','formulas'); // never let users create a default rule via the API
        
        $requestData['name']                 = substr($request->input('name'), 0, 50);
        $requestData['description']          = substr($request->input('description'), 0, 255);
        $requestData['alert_on_occurrences'] = $request->input('alert_on_occurrences', 1);

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

        $alertrule = Auth::user()->alert_rules()->create($requestData);

        $formulas = $request->input('formulas');
        foreach ($formulas as $f)
            $alertrule->formulas()->attach($f);

        return response()->json($alertrule, 201);
    }

    /**
     * api/alert-rules/{id} GET
     * Display the specified user alert rules.
     * @authenticated
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $alertrule = Auth::user()->alert_rules()->findOrFail($id);

        return $alertrule;
    }

    /**
     * api/alert-rules/{id} PATCH
     * Update the specified user alert rule.
     * @authenticated
     * @bodyParam name string The name of the alert rule. 
     * @bodyParam description string The description of the alert rule. 
     * @bodyParam measurement_id integer required The physical quantity / unit to alert for. 
     * @bodyParam calculation string required Calculation to be done with measurement value(s): (min, max, ave, der, cnt) -> Minimum, Maximum, Average (mean), Derivative, Count.
     * @bodyParam comparator string required Logical comparator to perform with comparison calculation result and threshold_value (=, <, >, <=, >=).
     * @bodyParam comparison string required Comparison function to perform with measurement value(s): (val, dif, abs, abs_dif) -> Value, Difference, Absolute value, Absolute value of the difference.
     * @bodyParam threshold_value float required The threshold value beyond which the alert will be sent. 
     * @bodyParam calculation_minutes integer The amount of minutes used for calculating the (min, max, ave, der, cnt) of the measurement value(s). If not provided, the last recorded value is used as a reference.
     * @bodyParam exclude_months array Array of month indexes (1-12). If not filled the standard alert is 'always on'. Example: [1,2,3,11,12]
     * @bodyParam exclude_hours array Array of hour indexes (0-23). If not filled the standard alert is 'always on'. Example: [0,1,2,3,22,23]
     * @bodyParam exclude_hive_ids array Array of Hive ids. If not filled the standard alert is evaluated on 'all hives'.
     * @bodyParam alert_on_occurrences integer Amount of occurences that a calculated value goed beyond the threshold_value. If not filled the standard is 1 (immediate alert).
     * @bodyParam alert_via_email boolean Set to false (0) if an e-mail should NOT be sent on alert. Default: true (1).
     * @bodyParam webhook_url string URL of optional endpoint to call on alert for web hook integration.
     * @bodyParam active boolean Set to false (0) if the alert should NOT be active. Default: true (1).
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'                      => 'nullable|string',
            'description'               => 'nullable|string',
            'formulas.*.alert_rule_id'  => 'nullable|integer|min:'.$id.'|max:'.$id,
            'formulas.*.measurement_id' => 'required|integer|exists:measurements,id',
            'formulas.*.calculation'    => ['required', Rule::in(array_keys(AlertRule::$calculations))],
            'formulas.*.comparator'     => ['required', Rule::in(array_keys(AlertRule::$comparators))],
            'formulas.*.comparison'     => ['required', Rule::in(array_keys(AlertRule::$comparisons))],
            'formulas.*.period_minutes' => 'required|integer|min:0',
            'formulas.*.threshold_value'=> 'required|numeric',
            'formulas.*.future'         => 'required|boolean',
            'formulas.*.logical'        => ['nullable', Rule::in(array_keys(AlertRuleFormula::$logicals))],
            'calculation_minutes'       => ['required', Rule::in(AlertRule::$calc_minutes)],
            'exclude_months.*'          => ['nullable', 'integer', Rule::in(array_keys(AlertRule::$exclude_months))],
            'exclude_hours.*'           => ['nullable', 'integer', Rule::in(array_keys(AlertRule::$exclude_hours))],
            'exclude_hive_ids.*'        => ['nullable', 'integer'/*, Rule::in($request->user()->allHives()->pluck('id'))*/],
            'alert_on_occurrences'      => 'nullable|integer',
            'alert_via_email'           => 'nullable|boolean',
            'webhook_url'               => 'nullable|url',
            'active'                    => 'nullable|boolean'
        ]);

        if ($validator->fails())
            return response()->json(['errors'=>$validator->errors()]);


        $alertrule   = Auth::user()->alert_rules()->findOrFail($id);
        $requestData = $request->except('default_rule','formulas'); // never let users create a default rule via the API

        $requestData['name']                 = substr($request->input('name'), 0, 50);
        $requestData['description']          = substr($request->input('description'), 0, 255);
        $requestData['alert_on_occurrences'] = $request->input('alert_on_occurrences', 1);

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

        $alertrule->update($requestData);

        $formulas = $request->input('formulas');
        $formulas_sync_array = [];
        foreach ($formulas as $f)
        {
            $f_id = $f['id'];
            unset($f['id']);
            $formulas_sync_array[$f_id] = $f;
        }
        $alertrule->formulas()->sync($formulas_sync_array);

        return response()->json($alertrule, 200);
    }

    /**
     * api/alert-rules/{id} DELETE
     * Delete the specified user alert rule.
     * @authenticated
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Auth::user()->alert_rules()->findOrFail($id)->delete();

        return response()->json(null, 204);
    }
}
