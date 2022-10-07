<?php

namespace App\Http\Controllers\Api;

use Auth;
use App\SampleCode;
use Illuminate\Http\Request;

/**
 * @group Api\SampleCodeController
 * Research lab result sample code controller
 * @authenticated
 */
class SampleCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $code = Auth::user()->samplecodes()->get();

        if ($code)
            return response()->json($code, 200);

        return response()->json(null, 404);
    }

    /**
    api/samplecode POST
    Create a sample code for lab results
    @authenticated
    **/
    public function store(Request $request)
    {
        if($request->filled('hive_id') && $request->user()->allHives()->find($request->input('hive_id')))
        {
            try{
                $code                = $request->only('hive_id', 'queen_id');
                $sample_time         = $request->filled('sample_date') ? strtotime($request->input('sample_date')) : time();
                $code['sample_date'] = date('Y-m-d H:i:s', $sample_time);
                $code['sample_code'] = SampleCode::generate_code();
                $code['user_id']     = $request->user()->id;
                $samplecode          = SampleCode::create($code);
            } catch (Exception $e) {
                Log::error("Api/SampleCodeController.store error: ".$e->getMessage());
            }
            return response()->json($samplecode, 201);
        }
        return response()->json(null, 400);
    }

    /**
     * Show not used
     */
    public function show(SampleCode $sampleCode)
    {
        //
    }

    /**
     * Update not used
     */
    public function update(Request $request, SampleCode $sampleCode)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SampleCode  $sampleCode
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if($request->filled('sample_code'))
        {
            $code = $request->user()->samplecodes()->where('sample_code', $request->input('sample_code'))->first();
            if ($code)
            {
                $code->delete();
                return response()->json('code_deleted', 200);
            }
        }
        return response()->json(null, 400);
    }
}
