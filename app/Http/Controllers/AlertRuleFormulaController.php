<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Models\AlertRuleFormula;
use Illuminate\Http\Request;

class AlertRuleFormulaController extends Controller
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
            $alertruleformula = AlertRuleFormula::where('alert_rule_id', 'LIKE', "%$keyword%")
                ->orWhere('measurement_id', 'LIKE', "%$keyword%")
                ->orWhere('calculation', 'LIKE', "%$keyword%")
                ->orWhere('comparator', 'LIKE', "%$keyword%")
                ->orWhere('comparison', 'LIKE', "%$keyword%")
                ->orWhere('logical', 'LIKE', "%$keyword%")
                ->orWhere('period_minutes', 'LIKE', "%$keyword%")
                ->orWhere('threshold_value', 'LIKE', "%$keyword%")
                ->paginate($perPage);
        } else {
            $alertruleformula = AlertRuleFormula::paginate($perPage);
        }

        return view('alert-rule-formula.index', compact('alertruleformula'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $alertruleformula = new AlertRuleFormula();
        return view('alert-rule-formula.create');
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
			'calculation' => 'required',
			'comparator' => 'required',
			'comparison' => 'required',
			'threshold_value' => 'required'
		]);
        $requestData = $request->all();
        
        AlertRuleFormula::create($requestData);

        return redirect('alert-rule-formula')->with('flash_message', 'AlertRuleFormula added!');
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
        $alertruleformula = AlertRuleFormula::findOrFail($id);

        return view('alert-rule-formula.show', compact('alertruleformula'));
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
        $alertruleformula = AlertRuleFormula::findOrFail($id);

        return view('alert-rule-formula.edit', compact('alertruleformula'));
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
			'calculation' => 'required',
			'comparator' => 'required',
			'comparison' => 'required',
			'threshold_value' => 'required'
		]);
        $requestData = $request->all();
        
        $alertruleformula = AlertRuleFormula::findOrFail($id);
        $alertruleformula->update($requestData);

        return redirect('alert-rule-formula')->with('flash_message', 'AlertRuleFormula updated!');
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
        AlertRuleFormula::destroy($id);

        return redirect('alert-rule-formula')->with('flash_message', 'AlertRuleFormula deleted!');
    }
}
