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
        $researches = Research::where('visible', true)->get();
        $out        = [];
        $user_id    = $request->user()->id;

        foreach ($researches as $r)
            if ($user_id == $r->user_id || $r->on_invite_only == false || (isset($r->default_user_ids) && in_array($user_id, $r->default_user_ids))) // owners, open researches and researches where user has been invited to
                $out[] = $r;

        return $out;
    }

    /**
     * research/{id}/add_consent POST
     * 
     * @urlParam id integer required Research ID
     * @bodyParam location_ids array Only share data from these location IDs
     * @bodyParam hive_ids array Only share data from these hive IDs
     * @bodyParam device_ids array Only share data from these device IDs
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

        // Provide consent on specific parts
        $loc_ids   = $request->filled('location_ids') && is_array($request->input('location_ids')) ? implode(',', $request->input('location_ids')) : null;
        $hive_ids  = $request->filled('hive_ids') && is_array($request->input('hive_ids')) ? implode(',', $request->input('hive_ids')) : null;
        $device_ids= $request->filled('device_ids') && is_array($request->input('device_ids')) ? implode(',', $request->input('device_ids')) : null;

        if ($history->count() > 0)
        {
            $last       = $history->first()->updated_at;
            $lastMoment = new Moment($last);
            $fromMoment = $lastMoment->from($timestamp);


            //die(print_r([$timestamp, $last, $fromMoment]));

            if ($fromMoment->getSeconds() < 3600)
            {
                DB::table('research_user')->where('research_id', $id)->where('user_id', $request->user()->id)->where('updated_at', $last)->update(['consent'=>$consent, 'updated_at'=>$timestamp, 'consent_location_ids'=>$loc_ids, 'consent_hive_ids'=>$hive_ids, 'consent_sensor_ids'=>$device_ids]);
                $updated = true;
            }
        }

        if ($updated === false)
            $request->user()->researches()->attach($id, ['consent'=>$consent, 'created_at'=>$timestamp, 'updated_at'=>$timestamp, 'consent_location_ids'=>$loc_ids, 'consent_hive_ids'=>$hive_ids, 'consent_sensor_ids'=>$device_ids]);

        return $research;
    }

}
