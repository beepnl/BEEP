<?php

namespace App\Http\Controllers;

use App\PhysicalQuantity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhysicalQuantityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $keyword = $request->get('search');
        $perPage = 1000;

        if (! empty($keyword)) {
            $physicalquantity = PhysicalQuantity::paginate($perPage);
        } else {
            $physicalquantity = PhysicalQuantity::paginate($perPage);
        }

        return view('physicalquantity.index', compact('physicalquantity'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('physicalquantity.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request): RedirectResponse
    {

        $requestData = $request->all();

        PhysicalQuantity::create($requestData);

        return redirect('physicalquantity')->with('flash_message', 'PhysicalQuantity added!');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View
    {
        $physicalquantity = PhysicalQuantity::findOrFail($id);

        return view('physicalquantity.show', compact('physicalquantity'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $physicalquantity = PhysicalQuantity::findOrFail($id);

        return view('physicalquantity.edit', compact('physicalquantity'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, int $id): RedirectResponse
    {

        $requestData = $request->all();

        $physicalquantity = PhysicalQuantity::findOrFail($id);
        $physicalquantity->update($requestData);

        return redirect('physicalquantity')->with('flash_message', 'PhysicalQuantity updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(int $id): RedirectResponse
    {
        PhysicalQuantity::destroy($id);

        return redirect('physicalquantity')->with('flash_message', 'PhysicalQuantity deleted!');
    }
}
