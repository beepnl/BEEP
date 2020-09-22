<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\User;
use App\Research;
use App\Location;
use App\Hive;
use App\Inspection;
use App\Device;

use DB;
use InfluxDB;
use Moment\Moment;

class ResearchController extends Controller
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
            $research = Research::where('description', 'LIKE', "%$keyword%")
                ->orWhere('name', 'LIKE', "%$keyword%")
                ->orWhere('url', 'LIKE', "%$keyword%")
                ->orWhere('type', 'LIKE', "%$keyword%")
                ->orWhere('institution', 'LIKE', "%$keyword%")
                ->orWhere('type_of_data_used', 'LIKE', "%$keyword%")
                ->orWhere('start_date', 'LIKE', "%$keyword%")
                ->orWhere('end_date', 'LIKE', "%$keyword%")
                ->orWhere('checklist_id', 'LIKE', "%$keyword%")
                ->paginate($perPage);
        } else {
            $research = Research::paginate($perPage);
        }

        return view('research.index', compact('research'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $research = new Research();
        return view('research.create', compact('research'));
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
            'name'          => 'required|string',
            'url'           => 'nullable|url',
            'image'         => 'nullable|image|max:2000',
            'start_date'    => 'nullable|date',
            'end_date'      => 'nullable|date|after:start',
            'checklist_ids' => 'nullable|exists:checklists,id',
        ]);

        $requestData = $request->all();

        if (isset($requestData['image']))
        {
            $image = Research::storeImage($requestData);
            if ($image)
            {
                $requestData['image_id'] = $image->id;
                unset($requestData['image']);
            }
        }

        $research = Research::create($requestData);

        if (isset($requestData['checklist_ids']))
            $research->checklists()->sync($requestData['checklist_ids']);

        return redirect('research')->with('flash_message', 'Research added!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function show($id, Request $request)
    {
        $research = Research::findOrFail($id);
        $influx   = new \Influx;

        // Make dates table
        $dates = [];

        $moment_start = new Moment($research->start_date);
        $moment_end   = new Moment($research->end_date);
        $moment_now   = new Moment();

        if ($moment_now < $moment_end)
            $moment_end = $moment_now;
            
        $moment_start = $moment_start->endof('day');
        $moment_end   = $moment_end->endof('day');
        $moment = $moment_start->startof('day');
        $assets = ["users"=>0, "apiaries"=>0, "hives"=>0, "inspections"=>0, "devices"=>0, "measurements"=>0];

        while($moment < $moment_end)
        {
            // make date
            $dates[$moment->format('Y-m-d')] = $assets;
            // next
            $moment = $moment->addDays(1);
        }

        // count user consents within dates
        $consent_users_select = DB::table('research_user')->join('users', 'users.id', '=', 'research_user.user_id')->where('research_user.research_id', $id)->whereDate('research_user.updated_at', '<', $research->end_date)->groupBy('research_user.user_id')->pluck('users.name','users.id')->toArray();
        asort($consent_users_select, SORT_NATURAL);
        
        $consent_users_selected = null;

        // select users
        if ($request->has('user_ids'))
            $consent_users_selected = $request->input('user_ids');
        else
            $consent_users_selected = [array_keys($consent_users_select)[0]];

        $consent_users = DB::table('research_user')->where('research_id', $id)->whereIn('user_id', $consent_users_selected)->whereDate('updated_at', '<', $research->end_date)->get();
        

        foreach ($consent_users as $cu) 
        {
            $user_consents   = DB::table('research_user')->where('research_id', $id)->where('user_id', $cu->user_id)->whereDate('updated_at', '<', $research->end_date)->orderBy('updated_at','asc')->get()->toArray();
            
            //die(print_r($consents));
            $user_consent      = $user_consents[0]->consent;
            $date_next_consent = $moment_end->format('Y-m-d');
            $date_curr_consent = $moment_end->format('Y-m-d');
            $index             = 0;

            if (count($user_consents) > 1)
            {
                $date_next_consent = substr($user_consents[1]->updated_at, 0, 10);
                $index             = 1;
            }
            elseif ($user_consent === 0) // if only 1 and consent is false, continue to next user
            {
                continue;
            }

            // add user data
            $user_apiaries     = Location::where('user_id', $cu->user_id)->orderBy('created_at')->get();
            $user_hives        = Hive::where('user_id', $cu->user_id)->orderBy('created_at')->get();
            $user_inspections  = User::find($cu->user_id)->inspections()->orderBy('created_at')->get();
            $user_devices      = Device::where('user_id', $cu->user_id)->orderBy('created_at')->get();

            // go over dates, compare consent dates
            foreach ($dates as $d => $v) 
            {
                if ($d >= $date_next_consent && $index > 0 && $index < count($user_consents)-1) // change user_consent if multiple user_consents exist and check date is past the active consent date 
                {
                    // take current user_consent
                    $user_consent       = $user_consents[$index]->consent;
                    $date_curr_consent  = substr($user_consents[$index]->updated_at, 0, 10);
                    //fill up to next consent date
                    $date_next_consent  = substr($user_consents[$index+1]->updated_at, 0, 10);
                    $index++;
                }

                if ($user_consent)
                {
                    $user_measurements = 0;

                    if (count($user_devices) > 0)
                    {
                        $points = [];
                        $where  = [];
                        foreach ($user_devices as $device) 
                        {
                            $key    = $device->key;
                            $where[]= '"key" = \''.$key.'\' OR "key" = \''.strtolower($key).'\' OR "key" = \''.strtoupper($key).'\'';
                        }
                        $where = '('.implode(' OR ', $where).')';
                        $user_sensor_query = $influx::query('SELECT COUNT(*) as "count" FROM "sensors" WHERE '.$where.' AND time >= \''.$d." 00:00:00".'\' AND time <= \''.$d." 23:59:59".'\'')->getPoints();
                        try{
                            $result  = $influx::query($user_sensor_query);
                            $points = $result->getPoints();
                        } catch (InfluxDB\Exception $e) {
                            // return Response::json('influx-group-by-query-error', 500);
                        } catch (Exception $e) {
                            // return Response::json('influx-group-by-query-error', 500);
                        }
                        if (count($points) > 0 && $points[0]['count'] > 0)
                            die(print_r($points)); //$user_measurements = $points[0]['count'];
                    }

                    $dates[$d]['users']       = $v['users'] + $user_consent;
                    $dates[$d]['apiaries']    = $v['apiaries'] + $user_apiaries->where('created_at', '<=', $d)->count();
                    $dates[$d]['hives']       = $v['hives'] + $user_hives->where('created_at', '<=', $d)->count();
                    $dates[$d]['inspections'] = $v['inspections'] + $user_inspections->where('created_at', '>=', $d.' 00:00:00')->where('created_at', '<=', $d.' 23:59:59')->count();
                    $dates[$d]['devices']     = $v['devices'] + $user_devices->where('created_at', '<=', $d)->count();
                    $dates[$d]['measurements']= $v['measurements'] + $user_measurements;
                }
            }
            //die(print_r([$user_consent, $date_next_consent, $user_consents, $dates]));
        }

        // reverse array for display
        krsort($dates);

        return view('research.show', compact('research', 'dates', 'consent_users_select', 'consent_users_selected'));
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
        $research = Research::findOrFail($id);

        return view('research.edit', compact('research'));
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
            'name'          => 'required|string',
            'url'           => 'nullable|url',
            'image'         => 'nullable|image|max:2000',
            'start_date'    => 'nullable|date',
            'end_date'      => 'nullable|date|after:start',
            'checklist_ids' => 'nullable|exists:checklists,id',
        ]);

        $requestData = $request->all();
        
        if (isset($requestData['image']))
        {
            $image = Research::storeImage($requestData);
            if ($image)
            {
                $requestData['image_id'] = $image->id;
                unset($requestData['image']);
            }
        }

        $research = Research::findOrFail($id);
        $research->update($requestData);

        if (isset($requestData['checklist_ids']))
            $research->checklists()->sync($requestData['checklist_ids']);

        return redirect('research')->with('flash_message', 'Research updated!');
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
        Research::destroy($id);

        return redirect('research')->with('flash_message', 'Research deleted!');
    }
}
