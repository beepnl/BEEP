<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Research;
use App\User;
use App\Location;
use App\Hive;
use App\Device;
use App\Inspection;
use Moment\Moment;
use Response;
use DB;

/**
 * @group Api\ResearchDataController
 * Retreive owned or viewable Research data
 */
class ResearchDataController extends Controller
{
    
    // Research API for researchers
    private function checkAuthorization(Request $request, $id=null)
    {
        
        if ($request->user()->researchMenuOption() == false)
            return false;

        if ($id)
            if (Research::findOrFail($id)->viewers()->where('users.id', $request->user()->id)->count() == 0)
                return false;
        
        return true;
    }

    /**
    api/researchdata GET
    List all available Researches
    @authenticated
    */
    public function index(Request $request)
    {
        $auth = $this->checkAuthorization($request);
        if ($auth == false)
            return Response::json('unauthorized', 405);

        if ($request->user()->hasRole('superadmin'))
            $researches = Research::all();
        else
            $researches = $request->user()->allResearches()->get();

        return Response::json($researches);
    }

    /**
    api/researchdata/{id} GET
    List one Research by id with list of consent_users
    @authenticated
    @queryParam id required The research ID to request data from. 
    */
    public function show(Request $request, $id)
    {
        $auth = $this->checkAuthorization($request, $id);
        if ($auth == false)
            return Response::json('unauthorized-for-research', 405);

        $research      = $request->user()->allResearches()->findOrFail($id);
        $consent_users = DB::table('research_user')
                            ->join('users', 'users.id', '=', 'research_user.user_id')
                            ->select('users.id','users.name','users.email','users.created_at','users.updated_at','users.last_login','users.locale','research_user.updated_at','research_user.consent')
                            ->where('research_user.research_id', $id)
                            ->whereDate('research_user.updated_at', '<', $research->end_date)
                            ->get();

        return Response::json(['research'=>$research, 'consent_users'=>$consent_users]);
    }

    /**
    api/researchdata/{id}/user/{user_id}/{item} GET
    List all user 'item' data within the consent=1 periods of a specific user within a Research.
    @authenticated
    @queryParam id required The research ID to request data from. 
    @queryParam user_id required The user id to request data from. 
    @queryParam item required The type of user data (apiaries/hives/devices/inspections/measurements) to request within the research (which the user gave consent for to use). 
    @bodyParam date_start datetime The date in 'YYYY-MM-DD HH:mm:ss' format (2020-01-01 00:00:00) to request data from (default is beginning of research, or earlier (except inspections and measurements). 
    @bodyParam date_until datetime The date in 'YYYY-MM-DD HH:mm:ss' format (2020-09-29 23:59:59) to request data until (default is until the end of the user consent, or research end). 
    */
    public function user_data(Request $request, $id, $user_id, $item)
    {
        $auth = $this->checkAuthorization($request, $id);
        if ($auth == false)
            return Response::json('unauthorized-for-research', 405);

        // Check if user is present on 
        if (DB::table('research_user')->where('research_id', $id)->where('user_id', $user_id)->where('consent', 1)->count() == 0)
            return Response::json('user-not-connected-to-research', 400);

        // Make dates
        $research   = Research::findOrFail($id);
        $date_start = $request->input('date_start', $research->start_date);
        $date_until = $request->input('date_until', $research->end_date);
        $date_format='Y-m-d H:i:s'; // RFC3339 == 'Y-m-d\TH:i:sP'
            
        if ($request->has('date_start'))
            if ($this->validateDate($date_start, $date_format) == false)
                return Response::json(['date_start_invalid'=>$date_start, 'format'=>$date_format], 400);
            else if ($date_start < $research->start_date)
                return Response::json('date_start_before_research_start', 400);

        if ($request->has('date_until'))
            if ($this->validateDate($date_until, $date_format) == false)
                return Response::json(['date_until_invalid'=>$date_until, 'format'=>$date_format], 400);
            else if ($date_until > $research->end_date)
                return Response::json('date_until_after_research_end', 400);
            else if ($date_until < $date_start)
                return Response::json('date_until_after_start_date', 400);
            else if ($date_start > $date_until)
                return Response::json('date_start_after_until_date', 400);

        // User specific data
        $user_consents     = DB::table('research_user')->where('research_id', $id)->where('user_id', $user_id)->whereDate('updated_at', '<', $date_until)->orderBy('updated_at','asc')->get()->toArray();
        $user_consent      = $user_consents[0]->consent;
        $date_curr_consent = $user_consents[0]->updated_at;
        $date_next_consent = $date_until;

        if (count($user_consents) == 0 || (count($user_consents) == 1 && $user_consent === 0)) // if only 1 and consent is false, stop
            return Response::json('user-gave-no-consent', 400);
        //die(print_r([$user_consents, $date_curr_consent, $date_next_consent, $index]));

        // Get all user data
        $user_apiaries     = Location::where('user_id', $user_id)->where('created_at', '<', $date_until)->orderBy('created_at')->get();
        $user_hives        = Hive::where('user_id', $user_id)->where('created_at', '<', $date_until)->orderBy('created_at')->get();
        $user_devices      = Device::where('user_id', $user_id)->where('created_at', '<', $date_until)->orderBy('created_at')->get();
        $user_inspections  = User::findOrFail($user_id)->inspections()->with('items')->where('created_at', '>=', $date_start)->where('created_at', '<', $date_until)->orderBy('created_at')->get();
        $user_measurements = [];
        
        $data = [];

        foreach ($user_consents as $i => $consent) 
        {
            $user_consent       = $consent->consent;
            $date_curr_consent  = $consent->updated_at;
            
            if ($i < count($user_consents)-1)
            {
                $date_next_consent = $user_consents[$i+1]->updated_at;
                $next_consent      = $user_consents[$i+1]->consent;
            }
            else // until end of research no other consent filled
            {
                $date_next_consent = $date_until;
                $next_consent      = $user_consent;
            }

            //print_r([$i, $user_consent, $date_curr_consent, $date_next_consent]);
            if ($request->has('date_start') && $date_start > $date_curr_consent)
                if ($date_start < $date_next_consent)
                    $date_curr_consent = $date_start;
                else
                    continue;
                        
            if ($request->has('date_until') && $date_until < $date_next_consent)
                if ($date_until > $date_curr_consent)
                    $date_next_consent = $date_until;

            // Fill objects for consent period
            if ($user_consent && ($next_consent || $i == 0))
            {    
                // add 
                switch($item)
                {
                    case 'apiaries':
                        $data = array_merge($data, $user_apiaries->where('created_at', '<=', $date_next_consent)->toArray());
                        break;
                    case 'hives':
                        $data = array_merge($data, $user_hives->where('created_at', '<=', $date_next_consent)->toArray());
                        break;
                    case 'devices':
                        $data = array_merge($data, $user_devices->where('created_at', '<=', $date_next_consent)->toArray());
                        break;
                    case 'inspections':
                        $data = array_merge($data, $user_inspections->where('created_at', '>', $date_curr_consent)->where('created_at', '<=', $date_next_consent)->toArray());
                        break;
                    case 'measurements':
                        if ($user_devices->count() > 0)
                            foreach ($user_devices as $dev)
                                if ($dev->created_at < $date_next_consent)
                                    $data = array_merge( $this->getArrayFromInflux($dev, $date_curr_consent, $date_next_consent, '*') );

                        break;
                    default:
                        return Response::json('invalid_item', 400);
                }
            }
        }
        return Response::json($data);
    }

    private function validateDate($date, $format='Y-m-d H:i:s') // RFC3339 == 'Y-m-d\TH:i:sP'
    {
        $unix  = strtotime($date);
        return date($format, $unix) === $date ? true : false;
    }

    private function getArrayFromInflux(Device $device, $start, $end, $measurements='*')
    {
        $options = ['precision'=>'rfc3339'];
        
        if ($measurements == null || $measurements == '' || $measurements === '*')
            $sensor_measurements = '*';
        else
            $sensor_measurements = '"'.implode('","',$measurements).'"';

        $query = 'SELECT '.$sensor_measurements.' FROM "sensors" WHERE ("key" = \''.$device->key.'\' OR "key" = \''.strtolower($device->key).'\' OR "key" = \''.strtoupper($device->key).'\') AND time >= \''.$start.'\' AND time <= \''.$end.'\'';
        $data  = [];
        try{
            $client = new \Influx; 
            $data   = $client::query($query, $options)->getPoints(); // get first sensor date
        } catch (InfluxDB\Exception $e) {
            // do nothing
        }
        
        if (count($data) == 0)
            return [];

        return $data;
    }

}
