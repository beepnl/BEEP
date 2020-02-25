<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\SensorDefinition;
use Illuminate\Http\Request;

class SensorDefinitionController extends Controller
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
            $sensordefinition = SensorDefinition::where('offset', 'LIKE', "%$keyword%")
                ->orWhere('multiplier', 'LIKE', "%$keyword%")
                ->orWhere('sensor_id', 'LIKE', "%$keyword%")
                ->paginate($perPage);
        } else {
            $sensordefinition = SensorDefinition::paginate($perPage);
        }

        return view('sensordefinition.index', compact('sensordefinition'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('sensordefinition.create');
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
        
        SensorDefinition::create($requestData);

        return redirect('sensordefinition')->with('flash_message', 'SensorDefinition added!');
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
        $sensordefinition = SensorDefinition::findOrFail($id);

        return view('sensordefinition.show', compact('sensordefinition'));
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
        $sensordefinition = SensorDefinition::findOrFail($id);

        return view('sensordefinition.edit', compact('sensordefinition'));
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
        
        $sensordefinition = SensorDefinition::findOrFail($id);
        $sensordefinition->update($requestData);

        return redirect('sensordefinition')->with('flash_message', 'SensorDefinition updated!');
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
        SensorDefinition::destroy($id);

        return redirect('sensordefinition')->with('flash_message', 'SensorDefinition deleted!');
    }
}
