<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\HiveType;
use Illuminate\Http\Request;

class HiveTypeController extends Controller
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
            $hivetype = HiveType::paginate($perPage);
        } else {
            $hivetype = HiveType::paginate($perPage);
        }

        return view('hivetype.index', compact('hivetype'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('hivetype.create');
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
        
        HiveType::create($requestData);

        return redirect('hivetype')->with('flash_message', 'HiveType added!');
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
        $hivetype = HiveType::findOrFail($id);

        return view('hivetype.show', compact('hivetype'));
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
        $hivetype = HiveType::findOrFail($id);

        return view('hivetype.edit', compact('hivetype'));
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
        
        $hivetype = HiveType::findOrFail($id);
        $hivetype->update($requestData);

        return redirect('hivetype')->with('flash_message', 'HiveType updated!');
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
        HiveType::destroy($id);

        return redirect('hivetype')->with('flash_message', 'HiveType deleted!');
    }
}
