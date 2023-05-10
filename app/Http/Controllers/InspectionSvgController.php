<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Models\InspectionSvg;
use Illuminate\Http\Request;

class InspectionSvgController extends Controller
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
            $inspectionsvg = InspectionSvg::where('user_id', 'LIKE', "%$keyword%")
                ->orWhere('checklist_id', 'LIKE', "%$keyword%")
                ->orWhere('name', 'LIKE', "%$keyword%")
                ->orWhere('svg', 'LIKE', "%$keyword%")
                ->orWhere('pages', 'LIKE', "%$keyword%")
                ->orWhere('last_print', 'LIKE', "%$keyword%")
                ->paginate($perPage);
        } else {
            $inspectionsvg = InspectionSvg::paginate($perPage);
        }

        return view('inspection-svg.index', compact('inspectionsvg'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $inspectionsvg = new InspectionSvg();
        return view('inspection-svg.create');
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
			'user_id' => 'required',
			'checklist_id' => 'required'
		]);
        $requestData = $request->all();
        
        InspectionSvg::create($requestData);

        return redirect('inspection-svg')->with('flash_message', 'InspectionSvg added!');
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
        $inspectionsvg = InspectionSvg::findOrFail($id);

        return view('inspection-svg.show', compact('inspectionsvg'));
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
        $inspectionsvg = InspectionSvg::findOrFail($id);

        return view('inspection-svg.edit', compact('inspectionsvg'));
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
			'user_id' => 'required',
			'checklist_id' => 'required'
		]);
        $requestData = $request->all();
        
        $inspectionsvg = InspectionSvg::findOrFail($id);
        $inspectionsvg->update($requestData);

        return redirect('inspection-svg')->with('flash_message', 'InspectionSvg updated!');
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
        InspectionSvg::destroy($id);

        return redirect('inspection-svg')->with('flash_message', 'InspectionSvg deleted!');
    }
}
