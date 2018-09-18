<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\PhysicalQuantity;
use Illuminate\Http\Request;

class PhysicalQuantityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $keyword = $request->get('search');
        $perPage = 1000;

        if (!empty($keyword)) {
            $physicalquantity = PhysicalQuantity::paginate($perPage);
        } else {
            $physicalquantity = PhysicalQuantity::paginate($perPage);
        }

        return view('physicalquantity.index', compact('physicalquantity'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('physicalquantity.create');
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
        
        PhysicalQuantity::create($requestData);

        return redirect('physicalquantity')->with('flash_message', 'PhysicalQuantity added!');
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
        $physicalquantity = PhysicalQuantity::findOrFail($id);

        return view('physicalquantity.show', compact('physicalquantity'));
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
        $physicalquantity = PhysicalQuantity::findOrFail($id);

        return view('physicalquantity.edit', compact('physicalquantity'));
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
        
        $physicalquantity = PhysicalQuantity::findOrFail($id);
        $physicalquantity->update($requestData);

        return redirect('physicalquantity')->with('flash_message', 'PhysicalQuantity updated!');
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
        PhysicalQuantity::destroy($id);

        return redirect('physicalquantity')->with('flash_message', 'PhysicalQuantity deleted!');
    }
}
