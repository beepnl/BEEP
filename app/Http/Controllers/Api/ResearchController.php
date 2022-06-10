<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Research;
use Moment\Moment;
use DB;

/**
 * @group Api\ResearchController
 * Manage your research consent
 * @authenticated
 */
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
        return response()->json($this->save_consent($request, $id, true), 200);
    }

    public function remove_consent(Request $request, $id)
    {
        return response()->json($this->save_consent($request, $id, false), 200);
    }

    public function edit_consent(Request $request, $id, $consent_id)
    {
        $consent = DB::table('research_user')->where('user_id', $request->user()->id)->where('research_id', $id)->find($consent_id);
        
        $saved = false;
        if ($consent && $request->filled('updated_at'))
        {
            $timestamp = $request->input('updated_at');
            DB::table('research_user')->where('id', $consent_id)->update(['updated_at'=>$timestamp]);
            $saved = true;
        }
        return response()->json($saved, $saved ? 200 : 500);
    }

    public function delete_no_consent(Request $request, $id, $consent_id)
    {
        $consent = DB::table('research_user')->where('user_id', $request->user()->id)->where('research_id', $id)->find($consent_id);

        $deleted = false;
        if ($consent && $consent->consent == false)
        {
            DB::table('research_user')->where('id', $consent_id)->delete();
            $deleted = true;
        }

        return response()->json($deleted, $deleted ? 200 : 500);
    }

    private function save_consent(Request $request, $id, $consent)
    {
        $research  = Research::findOrFail($id);
        $timestamp = date('Y-m-d H:i:s');

        $history   = $research->getConsentHistoryAttribute();
        $updated   = false;

        if ($history->count() > 0)
        {
            $last       = $history->first()->updated_at;
            $lastMoment = new Moment($last);
            $fromMoment = $lastMoment->from($timestamp);

            //die(print_r([$timestamp, $last, $fromMoment]));

            if ($fromMoment->getSeconds() < 3600)
            {
                DB::table('research_user')->where('research_id', $id)->where('user_id', $request->user()->id)->where('updated_at', $last)->update(['consent'=>$consent, 'updated_at'=>$timestamp]);
                $updated = true;
            }
        }

        if ($updated === false)
            $request->user()->researches()->attach($id, ['consent'=>$consent, 'created_at'=>$timestamp, 'updated_at'=>$timestamp]);

        return $research;
    }

}
