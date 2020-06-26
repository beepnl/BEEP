<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Auth;
use App\SampleCode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SampleCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $samplecode = SampleCode::all();
        
        return view('sample-code.index', compact('samplecode'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $samplecode = new SampleCode();
        $samplecode->sample_code = SampleCode::generate_code();
        $samplecode->user_id = Auth::user()->id;
        $samplecode->hive_id = Auth::user()->hives->first()->id;
        $samplecode->queen_id = Auth::user()->queens->first()->id;
        return view('sample-code.create', compact('samplecode'));
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
			'sample_code' => 'required',
			'hive_id' => 'required'
		]);
        $requestData = $request->all();
        
        SampleCode::create($requestData);

        return redirect('sample-code')->with('flash_message', 'SampleCode added!');
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
        $samplecode = SampleCode::findOrFail($id);

        return view('sample-code.show', compact('samplecode'));
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
        $samplecode = SampleCode::findOrFail($id);

        return view('sample-code.edit', compact('samplecode'));
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
			'sample_code' => 'required',
			'hive_id' => 'required'
		]);
        $requestData = $request->all();
        
        $samplecode = SampleCode::findOrFail($id);
        $samplecode->update($requestData);

        return redirect('sample-code')->with('flash_message', 'SampleCode updated!');
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
        SampleCode::destroy($id);

        return redirect('sample-code')->with('flash_message', 'SampleCode deleted!');
    }
}
