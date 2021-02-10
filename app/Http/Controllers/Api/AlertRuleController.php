<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\AlertRule;
use Illuminate\Http\Request;
use Auth;

/**
 * @group Api\AlertRuleController
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
        $alert_rules = AlertRule::where('default_rule', 1);

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
     * @bodyParam alert_on_occurrences integer Amount of occurences that a calculated value goed beyond the threshold_value. If not filled the standard is 1 (immediate alert).
     * @bodyParam alert_via_email boolean Set to false (0) if an e-mail should NOT be sent on alert. Default: true (1).
     * @bodyParam webhook_url string URL of optional endpoint to call on alert for web hook integration.
     * @bodyParam active boolean Set to false (0) if the alert should NOT be active. Default: true (1).
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
			'measurement_id' => 'required|integer|exists:measurements,id',
			'calculation' => 'required',
			'comparator' => 'required',
			'comparison' => 'required',
			'threshold_value' => 'required|float'
		]);

        $data      = $request->except('default_rule'); // never let users create a default rule via the API
        $alertrule = Auth::user()->alert_rules()->create($data);

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
     * @bodyParam alert_on_occurrences integer Amount of occurences that a calculated value goed beyond the threshold_value. If not filled the standard is 1 (immediate alert).
     * @bodyParam alert_via_email boolean Set to false (0) if an e-mail should NOT be sent on alert. Default: true (1).
     * @bodyParam webhook_url string URL of optional endpoint to call on alert for web hook integration.
     * @bodyParam active boolean Set to false (0) if the alert should NOT be active. Default: true (1).
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
			'measurement_id' => 'required|integer|exists:measurements,id',
			'calculation' => 'required',
			'comparator' => 'required',
			'comparison' => 'required',
			'threshold_value' => 'required|float'
		]);
        $alertrule = Auth::user()->alert_rules()->findOrFail($id);
        $data      = $request->except('default_rule'); // never let users create a default rule via the API
        $alertrule->update($data);

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
