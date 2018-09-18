<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\BeeRace;
use Illuminate\Http\Request;

class BeeRaceController extends Controller
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
            $beerace = BeeRace::paginate($perPage);
        } else {
            $beerace = BeeRace::paginate($perPage);
        }

        return view('beerace.index', compact('beerace'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('beerace.create');
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
        
        BeeRace::create($requestData);

        return redirect('beerace')->with('flash_message', 'BeeRace added!');
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
        $beerace = BeeRace::findOrFail($id);

        return view('beerace.show', compact('beerace'));
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
        $beerace = BeeRace::findOrFail($id);

        return view('beerace.edit', compact('beerace'));
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
        
        $beerace = BeeRace::findOrFail($id);
        $beerace->update($requestData);

        return redirect('beerace')->with('flash_message', 'BeeRace updated!');
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
        BeeRace::destroy($id);

        return redirect('beerace')->with('flash_message', 'BeeRace deleted!');
    }
}
