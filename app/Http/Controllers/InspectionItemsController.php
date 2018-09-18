<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\InspectionItem;
use Illuminate\Http\Request;

class InspectionItemsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $keyword = $request->get('search');
        $perPage = 100;

        if (!empty($keyword)) {
            $inspectionitems = InspectionItem::where('value', 'LIKE', "%$keyword%")
                ->orWhere('inspection_id', 'LIKE', "%$keyword%")
                ->orWhere('category_id', 'LIKE', "%$keyword%")
                ->paginate($perPage);
        } else {
            $inspectionitems = InspectionItem::paginate($perPage);
        }

        return view('inspection-items.index', compact('inspectionitems'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('inspection-items.create');
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
        
        $requestData = $request->all();
        
        InspectionItem::create($requestData);

        return redirect('inspection-items')->with('flash_message', 'InspectionItem added!');
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
        $inspectionitem = InspectionItem::findOrFail($id);

        return view('inspection-items.show', compact('inspectionitem'));
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
        $inspectionitem = InspectionItem::findOrFail($id);

        return view('inspection-items.edit', compact('inspectionitem'));
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
        
        $requestData = $request->all();
        
        $inspectionitem = InspectionItem::findOrFail($id);
        $inspectionitem->update($requestData);

        return redirect('inspection-items')->with('flash_message', 'InspectionItem updated!');
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
        InspectionItem::destroy($id);

        return redirect('inspection-items')->with('flash_message', 'InspectionItem deleted!');
    }
}
