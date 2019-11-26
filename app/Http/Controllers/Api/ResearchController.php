<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Research;

class ResearchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return Research::all();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function add_consent(Request $request, $id)
    {
        $research = Research::findOrFail($id);
        $request->user()->researches()->attach($id);

        return response()->json($research, 200);
    }

    public function remove_consent(Request $request, $id)
    {
        $research = Research::findOrFail($id);
        $request->user()->researches()->detach($id);

        return response()->json($research, 200);
    }

}
