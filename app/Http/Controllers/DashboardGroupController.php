<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Models\DashboardGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Auth;
use Str;

class DashboardGroupController extends Controller
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
            $dashboardgroup = DashboardGroup::where('user_id', 'LIKE', "%$keyword%")
                ->orWhere('code', 'LIKE', "%$keyword%")
                ->orWhere('name', 'LIKE', "%$keyword%")
                ->orWhere('hive_ids', 'LIKE', "%$keyword%")
                ->orWhere('speed', 'LIKE', "%$keyword%")
                ->orWhere('interval', 'LIKE', "%$keyword%")
                ->orWhere('show_inspections', 'LIKE', "%$keyword%")
                ->orWhere('show_all', 'LIKE', "%$keyword%")
                ->orWhere('hide_measurements', 'LIKE', "%$keyword%")
                ->orWhere('logo_url', 'LIKE', "%$keyword%")
                ->paginate($perPage);
        } else {
            $dashboardgroup = DashboardGroup::paginate($perPage);
        }

        return view('dashboard-group.index', compact('dashboardgroup'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $dashboardgroup = new DashboardGroup();
        $dashboardgroup->user_id = Auth::user()->id;
        $dashboardgroup->code = strtoupper(Str::random(6));
        $hive_ids = Auth::user()->allHives()->pluck('name','id')->toArray();
        return view('dashboard-group.create', compact('hive_ids','dashboardgroup'));
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
			'code' => 'required',
			'hive_id.*' => 'required|exists:hives:id',
			'user_id' => 'required',
			'interval' => ['required', Rule::in(DashboardGroup::$intervals)],
			'speed' => 'required|integer'
		]);
        $requestData = $request->all();
        
        DashboardGroup::create($requestData);

        return redirect('dashboard-group')->with('flash_message', 'DashboardGroup added!');
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
        $dashboardgroup = DashboardGroup::findOrFail($id);

        return view('dashboard-group.show', compact('dashboardgroup'));
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
        $dashboardgroup = DashboardGroup::findOrFail($id);
        $hive_ids = Auth::user()->allHives()->pluck('name','id')->toArray();
        return view('dashboard-group.edit', compact('hive_ids','dashboardgroup'));
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
			'code' => 'required',
			'hive_ids' => 'required',
			'user_id' => 'required',
			'interval' => 'required',
			'speed' => 'required'
		]);
        $requestData = $request->all();
        
        $dashboardgroup = DashboardGroup::findOrFail($id);
        $dashboardgroup->update($requestData);

        return redirect('dashboard-group')->with('flash_message', 'DashboardGroup updated!');
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
        DashboardGroup::destroy($id);

        return redirect('dashboard-group')->with('flash_message', 'DashboardGroup deleted!');
    }
}
