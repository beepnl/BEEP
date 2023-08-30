<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Models\AlertRuleFormula;
use Illuminate\Http\Request;

class AlertRuleFormulaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $alert_rule_formula = Models\AlertRuleFormula::paginate(25);

        return $alert_rule_formula;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'alert_rule_id'         => 'required|integer|exists:alert_rules,id',
            'measurement_id'        => 'required|integer|exists:measurements,id',
            'calculation'           => ['required', Rule::in(array_keys(AlertRuleFormula::$calculations))],
            'comparator'            => ['required', Rule::in(array_keys(AlertRuleFormula::$comparators))],
            'comparison'            => ['required', Rule::in(array_keys(AlertRuleFormula::$comparisons))],
            'logical'               => ['nullable', Rule::in(array_keys(AlertRuleFormula::$logicals))],
            'period_minutes'        => 'required|integer',
            'threshold_value'       => 'required|numeric',
        ]);

        if ($validator->fails())
            return response()->json(['errors'=>$validator->errors()]);

        $alert_rule_formula = Models\AlertRuleFormula::create($request->all());

        return response()->json($alert_rule_formula, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $alert_rule_formula = Models\AlertRuleFormula::findOrFail($id);

        return $alert_rule_formula;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'alert_rule_id'         => 'required|integer|exists:alert_rules,id',
            'measurement_id'        => 'required|integer|exists:measurements,id',
            'calculation'           => ['required', Rule::in(array_keys(AlertRuleFormula::$calculations))],
            'comparator'            => ['required', Rule::in(array_keys(AlertRuleFormula::$comparators))],
            'comparison'            => ['required', Rule::in(array_keys(AlertRuleFormula::$comparisons))],
            'logical'               => ['nullable', Rule::in(array_keys(AlertRuleFormula::$logicals))],
            'period_minutes'        => 'required|integer',
            'threshold_value'       => 'required|numeric',
        ]);

        if ($validator->fails())
            return response()->json(['errors'=>$validator->errors()]);
        
        $alert_rule_formula = Models\AlertRuleFormula::findOrFail($id);
        $alert_rule_formula->update($request->all());

        return response()->json($alert_rule_formula, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Models\AlertRuleFormula::destroy($id);

        return response()->json(null, 204);
    }
}
