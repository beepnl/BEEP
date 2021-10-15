<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\User;
use App\Device;
use App\Measurement;
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
        $page        = $request->get('page');
        $keyword     = $request->get('search');
        $search_user = $request->get('user');
        $search_dev  = $request->get('device');
        $search_mid  = $request->get('measurement_id');
        $perPage     = 50;
        $defs        = SensorDefinition::where('id', '!=', null);

        if (!empty($search_mid)) 
        {
            $defs = $defs->where('input_measurement_id', $search_mid)->orWhere('output_measurement_id', $search_mid);
        }
 
        if (!empty($search_dev)) 
        {
            $dev_ids = Device::where('name', 'LIKE', "%$search_dev%")
                            ->orWhere('key', 'LIKE', "%$search_dev%")
                            ->orWhere('hardware_id', 'LIKE', "%$search_dev%")
                            ->orWhere('hardware_version', 'LIKE', "%$search_dev%")
                            ->pluck('id')
                            ->toArray();
            if ($dev_ids)
                $defs = $defs->whereIn('id', $dev_ids);
            
        }

        if (!empty($search_user)) 
        {
            $user = User::where('name', 'LIKE', "%$search_user%")
                        ->orWhere('email', 'LIKE', "%$search_user%")
                        ->orWhere('locale', 'LIKE', "%$search_user%")
                        ->orWhere('id', 'LIKE', "%$search_user%")
                        ->first();
            
            if ($user)
            {
                $dev_ids = $user->devices()->pluck('id')->toArray();
                $defs       = $defs->whereIn('device_id', $dev_ids);
            }

        }

        if (!empty($keyword)) 
        {
            $defs = $defs->where('name', 'LIKE', "%$keyword%");
        }

        $sensordefinition = $defs->orderBy('name')->paginate($perPage);


        return view('sensordefinition.index', compact('sensordefinition','search_mid','page'));
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
