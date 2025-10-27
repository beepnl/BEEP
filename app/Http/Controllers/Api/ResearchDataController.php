<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

use App\Research;
use App\User;
use App\Location;
use App\Hive;
use App\Device;
use App\Inspection;
use App\Measurement;
use App\Models\FlashLog;
use App\Models\AlertRule;
use Moment\Moment;
use Response;
use DB;
use Cache;
use InfluxDB;
use Storage;

/**
 * @group Api\ResearchDataController
 * Retreive owned or viewable Research data
 * @authenticated
 */
class ResearchDataController extends Controller
{
    
    protected $valid_sensors  = [];
    protected $output_sensors = [];
    protected $output_weather = [];
    protected $influx_limit   = 5000;
    protected $timeFormat     = 'Y-m-d H:i:s'; 

    public function __construct()
    {
        $this->valid_sensors  = Measurement::getValidMeasurements();
        $this->output_sensors = Measurement::getValidMeasurements(true);
        $this->output_weather = Measurement::getValidMeasurements(true, true);
        $this->client         = new \Influx;
        //die(print_r($this->valid_sensors));
    }

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

    private function cacheRequestRate($name)
    {
        Cache::remember($name.'-time', 86400, function () use ($name)
        { 
            Cache::forget($name.'-count'); 
            return time(); 
        });

        if (Cache::has($name.'-count'))
            Cache::increment($name.'-count');
        else
            Cache::put($name.'-count', 1);

    }

    /**
    api/researchdata GET
    List all available Researches
    @authenticated
    @response [
        {
            "id": 1,
            "created_at": "2020-02-25 03:01:57",
            "updated_at": "2020-11-13 17:08:31",
            "name": "B-GOOD",
            "url": "https://b-good-project.eu/",
            "description": "B-GOOD has the overall goal to provide guidance for beekeepers and help them make better and more informed decisions.",
            "type": "research-b-good",
            "institution": "Wageningen University & Research",
            "type_of_data_used": "Hive inspections, hive settings, BEEP base measurement data",
            "start_date": "2019-07-01 00:00:00",
            "end_date": "2023-06-30 00:00:00",
            "image_id": 1,
            "consent": true,
            "consent_history": [
                {
                    "id": 185,
                    "created_at": "2020-11-12 22:28:09",
                    "updated_at": "2020-06-12 22:28:00",
                    "user_id": 1,
                    "research_id": 1,
                    "consent": 1,
                    "consent_location_ids": null,
                    "consent_hive_ids": null,
                    "consent_sensor_ids": null
                },
                {
                    "id": 1,
                    "created_at": "2020-02-25 03:02:23",
                    "updated_at": "2020-05-27 03:03:00",
                    "user_id": 1,
                    "research_id": 1,
                    "consent": 0,
                    "consent_location_ids": null,
                    "consent_hive_ids": null,
                    "consent_sensor_ids": null
                },
                {
                    "id": 97,
                    "created_at": "2020-05-14 16:24:41",
                    "updated_at": "2020-03-14 16:24:00",
                    "user_id": 1,
                    "research_id": 1,
                    "consent": 1,
                    "consent_location_ids": null,
                    "consent_hive_ids": null,
                    "consent_sensor_ids": null
                }
            ],
            "checklist_names": [
                "1 Winter",
                "2 Varroa",
                "3 Summer+",
                "4 Summer",
                "5 Health"
            ],
            "thumb_url": "/storage/users/1/thumbs/research/6LJEp35dodWWtfxnm3xfRnL05qvvJrHbn8IXAJqNCFZj2vFjwyLXbmWscKVz.jpg",
            "image": {
                "id": 1,
                "created_at": "2020-02-25 03:01:57",
                "updated_at": "2020-02-25 03:01:57",
                "filename": "6LJEp35dodWWtfxnm3xfRnL05qvvJrHbn8IXAJqNCFZj2vFjwyLXbmWscKVz.jpg",
                "image_url": "/storage/users/1/images/research/6LJEp35dodWWtfxnm3xfRnL05qvvJrHbn8IXAJqNCFZj2vFjwyLXbmWscKVz.jpg",
                "thumb_url": "/storage/users/1/thumbs/research/6LJEp35dodWWtfxnm3xfRnL05qvvJrHbn8IXAJqNCFZj2vFjwyLXbmWscKVz.jpg",
                "description": "B-GOOD has the overall goal to provide guidance for beekeepers and help them make better and more informed decisions.",
                "type": "research",
                "height": 1271,
                "width": 1271,
                "size_kb": 51,
                "date": "2020-02-25 03:01:57",
                "hive_id": null,
                "category_id": null,
                "inspection_id": null
            }
        }
    ]
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
    @urlParam id required The research ID to request data from. 
    @response {
        "research": {
            "id": 1,
            "created_at": "2020-02-25 03:01:57",
            "updated_at": "2020-11-18 10:33:23",
            "name": "B-GOOD",
            "url": "https://b-good-project.eu/",
            "description": "B-GOOD has the overall goal to provide guidance for beekeepers and help them make better and more informed decisions.",
            "type": "research-b-good",
            "institution": "Wageningen University & Research",
            "type_of_data_used": "Hive inspections, hive settings, BEEP base measurement data",
            "start_date": "2019-07-01 00:00:00",
            "end_date": "2023-06-30 00:00:00",
            "image_id": 1,
            "consent": true,
            "consent_history": [
                {
                    "id": 185,
                    "created_at": "2020-11-12 22:28:09",
                    "updated_at": "2020-06-12 22:28:00",
                    "user_id": 1,
                    "research_id": 1,
                    "consent": 1,
                    "consent_location_ids": null,
                    "consent_hive_ids": null,
                    "consent_sensor_ids": null
                },
                {
                    "id": 1,
                    "created_at": "2020-02-25 03:02:23",
                    "updated_at": "2020-05-27 03:03:00",
                    "user_id": 1,
                    "research_id": 1,
                    "consent": 0,
                    "consent_location_ids": null,
                    "consent_hive_ids": null,
                    "consent_sensor_ids": null
                },
                {
                    "id": 97,
                    "created_at": "2020-05-14 16:24:41",
                    "updated_at": "2020-03-14 16:24:00",
                    "user_id": 1,
                    "research_id": 1,
                    "consent": 1,
                    "consent_location_ids": null,
                    "consent_hive_ids": null,
                    "consent_sensor_ids": null
                }
            ],
            "checklist_names": [
                "1 Winter",
                "2 Varroa",
                "3 Summer+",
                "4 Summer",
                "5 Health"
            ],
            "thumb_url": "/storage/users/1/thumbs/research/6LJEp35dodWWtfxnm3xfRnL05qvvJrHbn8IXAJqNCFZj2vFjwyLXbmWscKVz.jpg",
            "image": {
                "id": 1,
                "created_at": "2020-02-25 03:01:57",
                "updated_at": "2020-02-25 03:01:57",
                "filename": "6LJEp35dodWWtfxnm3xfRnL05qvvJrHbn8IXAJqNCFZj2vFjwyLXbmWscKVz.jpg",
                "image_url": "/storage/users/1/images/research/6LJEp35dodWWtfxnm3xfRnL05qvvJrHbn8IXAJqNCFZj2vFjwyLXbmWscKVz.jpg",
                "thumb_url": "/storage/users/1/thumbs/research/6LJEp35dodWWtfxnm3xfRnL05qvvJrHbn8IXAJqNCFZj2vFjwyLXbmWscKVz.jpg",
                "description": "B-GOOD has the overall goal to provide guidance for beekeepers and help them make better and more informed decisions.",
                "type": "research",
                "height": 1271,
                "width": 1271,
                "size_kb": 51,
                "date": "2020-02-25 03:01:57",
                "hive_id": null,
                "category_id": null,
                "inspection_id": null
            }
        },
        "consent_users": [
            {
                "id": 1,
                "name": "Beep",
                "email": "pim@beep.nl",
                "created_at": "2017-07-14 03:34:10",
                "updated_at": "2020-05-27 03:03:00",
                "last_login": "2020-11-18 10:32:16",
                "locale": null,
                "consent": 0
            },
            {
                "id": 2371,
                "name": "app@beep.nl",
                "email": "app@beep.nl",
                "created_at": "2019-10-24 17:15:55",
                "updated_at": "2020-02-25 11:46:59",
                "last_login": "2020-08-20 18:24:22",
                "locale": null,
                "consent": 0
            },
            {
                "id": 1,
                "name": "Beep",
                "email": "pim@beep.nl",
                "created_at": "2017-07-14 03:34:10",
                "updated_at": "2020-06-12 22:28:00",
                "last_login": "2020-11-18 10:32:16",
                "locale": null,
                "consent": 1
            }
        ]
    }
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
    List all user 'item' data within the consent=1 periods of a specific user within a Research. The 'item' field indicates the type of user data (apiaries/locations/hives/devices/flashlogs/inspections/measurements/weather) to request within the research (which the user gave consent for to use). Example: inspectionsResponse: api/researchdata/1/user/1/inspections. 
    @authenticated
    @authenticated
    @urlParam id required The research ID to request data from. Example: 1
    @urlParam user_id required The user id to request data from. Example: 1
    @urlParam item required The type of user data (locations/devices/inspections/measurements) to request within the research (which the user gave consent for to use). Example: inspections
    @bodyParam date_start datetime The date in 'YYYY-MM-DD HH:mm:ss' format (2020-01-01 00:00:00) to request data from (default is beginning of research, or earlier (except inspections and measurements). Example: 2020-01-01 00:00:00
    @bodyParam date_until datetime The date in 'YYYY-MM-DD HH:mm:ss' format (2020-09-29 23:59:59) to request data until (default is until the end of the user consent, or research end). Example: 2020-09-29 23:59:59
    @bodyParam interval string Specifies the optional (InfluxDB GROUPBY) time interval to interpolate measurements (*(all values)/1m/5m/30m/1h/1d/1w/30d/365d) m (minutes), h (hours), d (days), w (weeks). Default: 1d. Example: 5m 
    @bodyParam limit integer Specifies the maximum number of measurements per location_research (InfluxDB LIMIT), Max: 5000. Default: 5000. Example: 10 
    @bodyParam calculation string Specifies the optional (InfluxDB) calculation (NONE/FIRST/LAST/MEAN/MEDIAN/MIN/MAX/SUM/COUNT/SPREAD/STDDEV/DERIVATIVE/PERCENTILE/BOXPLOT/PEAKS/WEEKMAP/NETWEIGHT) for use with time interval. Default: NONE. Example: MEAN 
    @bodyParam calculation_prop string Specifies the optional (InfluxDB) calculation property for i.e. PERCENTILE/DERIVATIVE/etc). Default: null. Example: DERIVATIVE
    @bodyParam decimals integer Specifies the optional maximum amount of decimals that the (InfluxDB) calculation returns. Default: 2. Example: 1 
    @bodyParam device_id integer The device_id to filter the measurements on (next to date_start and date_until). Example: 1
    @bodyParam location_id integer The location_id to filter the hives, and measurements on (next to date_start and date_until). Example: 2
    @bodyParam precision string Specifies the optional InfluxDB format/precision (rfc3339/h/m/s/ms/u) of the timestamp of the measurements and weather data: rfc3339 (YYYY-MM-DDTHH:MM:SS.nnnnnnnnnZ), h (hours), m (minutes), s (seconds), ms (milliseconds), u (microseconds). Precision defaults to rfc3339. Example: rfc3339
    @bodyParam measurements string Comma separated string of measurements (e.g. weight_kg,t_i,t_0,t_1) to query. Default: all measurments available.
    @response [
        {
            "id": 35211,
            "notes": "test",
            "reminder": null,
            "reminder_date": null,
            "impression": 2,
            "attention": 1,
            "created_at": "2020-03-26 18:28:00",
            "checklist_id": 798,
            "image_id": null,
            "owner": true,
            "thumb_url": null,
            "hive_id": 280,
            "items": []
        },
        {
            "id": 40162,
            "notes": "Input Liebefeld",
            "reminder": null,
            "reminder_date": null,
            "impression": null,
            "attention": null,
            "created_at": "2020-04-24 11:03:00",
            "checklist_id": 3206,
            "image_id": null,
            "owner": true,
            "thumb_url": null,
            "hive_id": 280,
            "items": [
                {
                    "id": 326538,
                    "value": "0.6",
                    "inspection_id": 40162,
                    "category_id": 977,
                    "val": "0.6",
                    "unit": "x 25cm2",
                    "type": "square_25cm2"
                },
                {
                    "id": 326539,
                    "value": "4",
                    "inspection_id": 40162,
                    "category_id": 978,
                    "val": "4",
                    "unit": "x 25cm2",
                    "type": "square_25cm2"
                },
                {
                    "id": 326540,
                    "value": "2",
                    "inspection_id": 40162,
                    "category_id": 979,
                    "val": "2",
                    "unit": "x 25cm2",
                    "type": "square_25cm2"
                }
            ]
        },
        {
            "id": 40163,
            "notes": "Brood photograph",
            "reminder": null,
            "reminder_date": null,
            "impression": null,
            "attention": null,
            "created_at": "2020-04-24 11:07:00",
            "checklist_id": 3206,
            "image_id": null,
            "owner": true,
            "thumb_url": null,
            "hive_id": 280,
            "items": [
                {
                    "id": 326567,
                    "value": "1",
                    "inspection_id": 40163,
                    "category_id": 399,
                    "val": "Ja",
                    "unit": null,
                    "type": "boolean"
                },
                {
                    "id": 326568,
                    "value": "https://assets.beep.nl/users/1/thumbs/inspection/jIcycTYnO8zYq6SHCvAwPHb97BDLFkZaDmfZUop5.png",
                    "inspection_id": 40163,
                    "category_id": 973,
                    "val": "https://assets.beep.nl/users/1/thumbs/inspection/jIcycTYnO8zYq6SHCvAwPHb97BDLFkZaDmfZUop5.png",
                    "unit": null,
                    "type": "image"
                }
            ]
        },
        {
            "id": 68477,
            "notes": null,
            "reminder": null,
            "reminder_date": null,
            "impression": 3,
            "attention": 1,
            "created_at": "2020-10-23 12:43:00",
            "checklist_id": 3206,
            "image_id": null,
            "owner": true,
            "thumb_url": null,
            "hive_id": 281,
            "items": []
        },
        {
            "id": 68478,
            "notes": "Hive change",
            "reminder": null,
            "reminder_date": null,
            "impression": null,
            "attention": null,
            "created_at": "2020-10-23 13:12:33",
            "checklist_id": null,
            "image_id": null,
            "owner": true,
            "thumb_url": null,
            "hive_id": 281,
            "items": [
                {
                    "id": 522496,
                    "value": "2",
                    "inspection_id": 68478,
                    "category_id": 85,
                    "val": "2",
                    "unit": null,
                    "type": "number_positive"
                },
                {
                    "id": 522497,
                    "value": "2",
                    "inspection_id": 68478,
                    "category_id": 87,
                    "val": "2",
                    "unit": null,
                    "type": "number"
                },
                {
                    "id": 522498,
                    "value": "10",
                    "inspection_id": 68478,
                    "category_id": 89,
                    "val": "10",
                    "unit": null,
                    "type": "number_positive"
                }
            ]
        }
    ]
    */
    public function user_data(Request $request, $id, $user_id, $item)
    {
        $auth = $this->checkAuthorization($request, $id);
        if ($auth == false)
            return Response::json('unauthorized-for-research', 405);

        $this->validate($request, [
            'date_start'            => 'nullable|date',
            'date_until'            => 'nullable|date',
            'device_id'             => 'nullable|integer|exists:sensors,id',
            'location_id'           => 'nullable|integer|exists:locations,id',
            'measurements'          => 'nullable|string',
            'decimals'              => 'nullable|integer',
            'interval'              => ['nullable', Rule::in(['*','1m','5m','15m','30m','1h','3h','6h','12h','1d','1w','30d','365d','hour','day','week','month','year','research','live'])],
            'calculation'           => ['nullable', Rule::in(['NONE','FIRST','LAST','MEAN','MEDIAN','MIN','MAX','SUM','COUNT','SPREAD','STDDEV','DERIVATIVE','PERCENTILE','BOXPLOT','PEAKS','WEEKMAP','NETWEIGHT'])],
            'calculation_prop'      => 'nullable|string',
            'limit'                 => 'nullable|integer|min:1|max:'.$this->influx_limit,
            'precision'             => ['nullable', Rule::in(['rfc3339','h','m','s','ms','u'])],
            'index'                 => 'nullable|integer|min:0', // only in case of 1 device requested, to provide 'normal' data view
            'timezone'              => 'nullable|timezone',
        ]);

        // Check if user is present on 
        if (DB::table('research_user')->where('research_id', $id)->where('user_id', $user_id)->where('consent', 1)->count() == 0)
            return Response::json('user-not-connected-to-research', 400);

        if (!in_array($item, ['apiaries','locations','devices','inspections','measurements','weather']))
            return Response::json('invalid-item-requested', 400);

        // Make dates
        $research   = Research::findOrFail($id);
        $user       = User::findOrFail($user_id);
        $location_id= $request->input('location_id');
        $date_start = $request->input('date_start', $research->start_date);
        $date_until = $request->input('date_until', $research->end_date);
        $calculation= $request->input('calculation', 'NONE');
        $calc_prop  = $request->input('calculation_prop');
        $interval   = $request->input('interval', '1h');
        $decimals   = $request->input('decimals', 2);
        $limit      = $request->input('limit', $this->influx_limit);
        $precision  = $request->input('precision', 'rfc3339');
        $measurements=$request->filled('measurements') ? explode(',', $request->input('measurements')) : '*';
        $index      = $request->filled('index') ? intval($request->input('index',0)) : null;
        $timeZone   = $request->input('timezone','UTC');
        
        $date_format='Y-m-d H:i:s'; // RFC3339 == 'Y-m-d\TH:i:sP'
            
        if ($request->filled('date_start'))
        {
            if ($this->validateDate($date_start, $date_format) == false)
                return Response::json(['date_start_invalid'=>$date_start, 'format'=>$date_format], 400);
            else if ($date_start < $research->start_date)
                $date_start = $research->start_date; // return Response::json('date_start_before_research_start', 400);
        }

        if ($request->filled('date_until'))
        {
            if ($this->validateDate($date_until, $date_format) == false)
                return Response::json(['date_until_invalid'=>$date_until, 'format'=>$date_format], 400);
            else if ($date_until > $research->end_date)
                $date_until = $research->end_date; // return Response::json('date_until_after_research_end', 400);
            else if ($date_until < $date_start)
                return Response::json('date_until_before_start_date', 400);
            else if ($date_start > $date_until)
                return Response::json('date_start_after_until_date', 400);
        }

        // User specific data
        $user_consents     = DB::table('research_user')->where('research_id', $id)->where('user_id', $user_id)->whereDate('updated_at', '<', $date_until)->orderBy('updated_at','asc')->get()->toArray();

        if (count($user_consents) == 0) // if only 1 and consent is false, stop
            return Response::json('user-gave-no-consent', 400);

        $user_consent_obj  = $user_consents[0];
        $user_consent      = $user_consent_obj->consent;

        if(count($user_consents) == 1 && $user_consent === 0) // if only 1 and consent is false, stop
            return Response::json('user-gave-no-consent', 400);
        
        $date_curr_consent = $user_consent_obj->updated_at;
        $date_consent_start= $date_curr_consent < $date_start ? $date_start : $date_curr_consent; // take latest of two dates
        $date_next_consent = $date_until;
        //die(print_r([$user_consents, $date_curr_consent, $date_next_consent, $index]));

        if ($item == 'measurements' || $item == 'weather')
            $this->cacheRequestRate('get-measurements-research');
        
        // Get all user data
        if (isset($user_consent_obj->consent_location_ids)) // Set consent based on specific items
        {
            $loc_array     = explode(',', $user_consent_obj->consent_location_ids);
            $user_apiaries = $user->locations()->withTrashed()->whereIn('id', $loc_array)->where('created_at', '<', $date_until)->orderBy('created_at')->get();
        }
        else
        {
            $user_apiaries = $user->locations()->withTrashed()->where('created_at', '<', $date_until)->orderBy('created_at')->get();
        }

        if (isset($user_consent_obj->consent_hive_ids)) // Set consent based on specific items
        {
            $hive_array    = explode(',', $user_consent_obj->consent_hive_ids);
            $user_hives    = $user->hives()->withTrashed()->whereIn('id', $hive_array)->where('created_at', '<', $date_until)->orderBy('created_at')->get();
        }
        else
        {
            $user_hives    = $user->hives()->withTrashed()->where('created_at', '<', $date_until)->orderBy('created_at')->get();
        }

        if (isset($user_consent_obj->consent_sensor_ids)) // Set consent based on specific items
        {
            $device_array  = explode(',', $user_consent_obj->consent_sensor_ids);
            $user_devices  = $user->devices()->withTrashed()->whereIn('id', $device_array)->where('created_at', '<', $date_until)->orderBy('created_at')->get();
        }
        else
        {
            $user_devices  = $user->devices()->withTrashed()->where('created_at', '<', $date_until)->orderBy('created_at')->get();
        }


        $user_flashlogs    = FlashLog::where('user_id', $user_id)->where('created_at', '<', $date_until)->orderBy('created_at')->get();
        $user_measurements = [];

        // add hive inspections (also from collaborators)
        $hive_inspection_ids = [];
        foreach ($user_hives as $hive)
        {
            $hive_inspections = $hive->inspections()->where('created_at', '>=', $date_consent_start)->where('created_at', '<', $date_until)->get();
            foreach ($hive_inspections as $ins) 
                $hive_inspection_ids[] = $ins->id;
            
        }
        $hive_inspections = Inspection::whereIn('id', $hive_inspection_ids)->with('items')->where('created_at', '>=', $date_consent_start)->where('created_at', '<', $date_until)->orderBy('created_at')->get();

        
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

            // Minimize data to requested dates
            if ($request->filled('date_start') && $date_start > $date_curr_consent)
            {
                if ($date_start < $date_next_consent)
                    $date_curr_consent = $date_start;
                else
                    continue; // start >= next_consent, so hide this data from dataset, because earlier than requested
            }
                        
            if ($request->filled('date_until') && $date_until < $date_next_consent)
            {
                if ($date_until > $date_curr_consent)
                    $date_next_consent = $date_until;
                else
                    continue; // until <= curr_consent, so hide this data from dataset, because later than requested
            }

            // Fill objects for consent period
            if ($user_consent && ($next_consent || $i == 0))
            {    
                // add 
                switch($item)
                {
                    case 'locations':
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
                        $data = array_merge($data, $hive_inspections->where('created_at', '>', $date_curr_consent)->where('created_at', '<=', $date_next_consent)->toArray());
                        break;
                    case 'flashlogs':
                        $data = array_merge($data, $user_flashlogs->where('created_at', '<=', $date_next_consent)->toArray());
                        break;
                    case 'measurements':
                        if ($user_devices->count() > 0)
                        {
                            foreach ($user_devices as $device)
                            {
                                if (($request->filled('device_id') && $request->input('device_id') == $device->id) || !$request->filled('device_id'))
                                {   
                                    if ($device->created_at < $date_next_consent)
                                    {
                                        $where= $device->influxWhereKeys().' AND time >= \''.$date_curr_consent.'\' AND time <= \''.$date_next_consent.'\'';
                                        $adds = ['device_id'=>$device->id];
                                        $data = array_merge($data, $this->getArrayFromInflux($where, $measurements, 'sensors', $interval, $calculation, $calc_prop, $decimals, $precision, $adds, $limit, $device, $timeZone, $date_curr_consent, $date_next_consent)); // $where, $measurements='*', $database='sensors', $interval='1h', $calculation='MEAN', $calc_prop=null, $decimals=2, $precision='rfc3339', $adds=[], $limit=5000
                                    }
                                }
                            }
                        }
                        break;
                    case 'weather':
                        if ($user_apiaries->count() > 0)
                        {
                            foreach ($user_apiaries as $apiary)
                            {
                                if ($apiary->created_at < $date_next_consent)
                                {
                                    $where= '"lat" = \''.$apiary->coordinate_lat.'\' AND "lon" = \''.$apiary->coordinate_lon.'\' AND time >= \''.$date_curr_consent.'\' AND time <= \''.$date_next_consent.'\'';
                                    $adds = ['device_id'=>$device->id];
                                    $data = array_merge($data, $this->getArrayFromInflux($where, $measurements, 'weather', $interval, $calculation, $calc_prop, $decimals, $precision, $adds, $limit));
                                }
                            }
                        }
                        break;
                    default:
                        return Response::json('invalid_item', 400);
                }
            }
        }
        return Response::json($data);
    }
/**
    api/researchdata/{id}/data/{item} GET
    List all research 'item' data within the consent=1 periods within a Research. The 'item' field indicates the type of data (apiaries/locations/devices/inspections/measurements/weather) to request within the research (which the user gave consent for to use). Example: inspectionsResponse: api/researchdata/1/inspections. 
    @authenticated
    @urlParam id required The research ID to request data from. Example: 1
    @urlParam item required The type of user data (locations/devices/inspections/measurements) to request within the research (which the user gave consent for to use). Example: inspections
    @bodyParam date_start datetime The date in 'YYYY-MM-DD HH:mm:ss' format (2020-01-01 00:00:00) to request data from (default is beginning of research, or earlier (except inspections and measurements). Example: 2020-01-01 00:00:00
    @bodyParam date_until datetime The date in 'YYYY-MM-DD HH:mm:ss' format (2020-09-29 23:59:59) to request data until (default is until the end of the user consent, or research end). Example: 2020-09-29 23:59:59
    @bodyParam year_months string Comma separated string of YYYY-MM strings to filter ONLY measurment data. Example: 2020-01,2021-02
    @bodyParam interval string Specifies the optional (InfluxDB GROUPBY) time interval to interpolate measurements (*(all values)/1m/5m/30m/1h/1d/1w/30d/365d) m (minutes), h (hours), d (days), w (weeks). Default: 1d. Example: 5m 
    @bodyParam limit integer Specifies the maximum number of measurements per location_research (InfluxDB LIMIT), Max: 5000. Default: 5000. Example: 500 
    @bodyParam calculation string Specifies the optional (InfluxDB) calculation (NONE/FIRST/LAST/MEAN/MEDIAN/MIN/MAX/SUM/COUNT/SPREAD/STDDEV/DERIVATIVE/PERCENTILE/BOXPLOT/PEAKS/WEEKMAP/NETWEIGHT) for use with time interval. Default: MEAN. Example: MAX 
    @bodyParam calculation_prop string Specifies the optional (InfluxDB) calculation property for i.e. PERCENTILE/DERIVATIVE/etc). Default: null. Example: 5 
    @bodyParam decimals integer Specifies the optional maximum amount of decimals that the (InfluxDB) calculation returns. Default: 2. Example: 1 
    @bodyParam device_id integer The device_id to filter measurements on (next to date_start and date_until). Example: 1
    @bodyParam device_ids string Comma separated string of device_ids to filter the measurements on (next to date_start and date_until). Example: 1,3,6
    @bodyParam location_id integer The location_id to filter and measurements (next to date_start and date_until). Example: 2
    @bodyParam location_ids string Comma separated string of location_ids to filter measurements (next to date_start and date_until). Example: 1,3,6
    @bodyParam precision string Specifies the optional InfluxDB format/precision (rfc3339/h/m/s/ms/u) of the timestamp of the measurements and weather data: rfc3339 (YYYY-MM-DDTHH:MM:SS.nnnnnnnnnZ), h (hours), m (minutes), s (seconds), ms (milliseconds), u (microseconds). Precision defaults to rfc3339. Example: rfc3339
    @bodyParam measurements string Comma separated string of measurements (e.g. weight_kg,t_i,t_0,t_1) to query. Default: * (all measurments available).
    @bodyParam index integer Historic index of the interval from now. 0=period with current time included. 1=previous interval. Required without end. Example: 0
    @bodyParam timezone string Provide the front-end timezone to correct the time from UTC to front-end time. Example: Europe/Amsterdam
    @bodyParam output_csv_links boolean Optionally provide true if you want the data to be returned as an array of CSV files in stead of JSON data.
    */
    public function research_data(Request $request, $id, $item)
    {
        $auth = $this->checkAuthorization($request, $id);
        if ($auth == false)
            return Response::json('unauthorized-for-research', 405);

        $this->validate($request, [
            'date_start'            => 'nullable|date',
            'date_until'            => 'nullable|date',
            'year_months'           => 'nullable|string',
            'device_ids'            => 'nullable|string',
            'device_id'             => 'nullable|integer|exists:sensors,id',
            'location_ids'          => 'nullable|string',
            'location_id'           => 'nullable|integer|exists:locations,id',
            'measurements'          => 'nullable|string',
            'decimals'              => 'nullable|integer',
            'interval'              => ['nullable', Rule::in(['*','1m','5m','15m','30m','1h','3h','6h','12h','1d','1w','30d','365d','hour','day','week','month','year','research','live'])],
            'calculation'           => ['nullable', Rule::in(['NONE','FIRST','LAST','MEAN','MEDIAN','MIN','MAX','SUM','COUNT','SPREAD','STDDEV','DERIVATIVE','PERCENTILE','BOXPLOT','PEAKS','WEEKMAP','NETWEIGHT'])],
            'calculation_prop'      => 'nullable|string',
            'limit'                 => 'nullable|integer|min:1|max:'.$this->influx_limit,
            'precision'             => ['nullable', Rule::in(['rfc3339','h','m','s','ms','u'])],
            'index'                 => 'nullable|integer|min:0', // only in case of 1 device requested, to provide 'normal' data view
            'timezone'              => 'nullable|timezone',
            'output_csv_links'      => 'nullable|boolean',
        ]);

        // Check if user is present on 
        if (DB::table('research_user')->where('research_id', $id)->where('consent', 1)->count() == 0)
            return Response::json('no-user-consents-for-research', 400);

        if (!in_array($item, ['apiaries','locations','devices','inspections','measurements','weather']))
            return Response::json('invalid-item-requested', 400);

        // Make dates
        $research   = Research::findOrFail($id);
        
        $location_id= $request->input('location_id');
        $device_id  = $request->input('device_id');
        
        $date_format='Y-m-d H:i:s'; // RFC3339 == 'Y-m-d\TH:i:sP'
        $date_start = $request->input('date_start', $research->start_date);
        $date_until = $request->input('date_until', $research->end_date);

        $calculation= $request->input('calculation', 'MEAN');
        $calc_prop  = $request->input('calculation_prop');
        $interval   = $request->input('interval', '1d');
        $decimals   = $request->input('decimals', 2);
        $limit      = $request->input('limit', $this->influx_limit);
        $precision  = $request->input('precision', 'rfc3339');
        
        $measurements=$request->filled('measurements')          ? explode(',', $request->input('measurements')) : '*';
        $location_ids=$request->filled('location_ids')          ? explode(',', $request->input('location_ids')) : (isset($location_id) ? [$location_id] : null);
        $device_ids = $request->filled('device_ids')            ? explode(',', $request->input('device_ids')) : (isset($device_id) ? [$device_id] : null);
        $year_months= $request->filled('year_months') ? explode(',', $request->input('year_months')) : null; // ['2021-03',2022-03'];
        $index      = $request->filled('index') ? intval($request->input('index',0)) : null;
        $timeZone   = $request->input('timezone','UTC');
        $data_call  = in_array($interval, ['hour','day','week','month','year','research','live']);
        $m_start    = $date_start;
        $m_until    = $date_until;
        $csv_output = $request->filled('output_csv_links') ? boolval($request->input('output_csv_links')) : false;
            
        if ($request->filled('date_start'))
        {
            if ($this->validateDate($date_start, $date_format) == false)
                return Response::json(['date_start_invalid'=>$date_start, 'format'=>$date_format], 400);
            else if ($date_start < $research->start_date)
                $date_start = $research->start_date; // return Response::json('date_start_before_research_start', 400);
        }
        if ($request->filled('date_until'))
        {
            if ($this->validateDate($date_until, $date_format) == false)
                return Response::json(['date_until_invalid'=>$date_until, 'format'=>$date_format], 400);
            else if ($date_until > $research->end_date)
                $date_until = $research->end_date; // return Response::json('date_until_after_research_end', 400);
            else if ($date_until < $date_start)
                return Response::json('date_until_before_start_date', 400);
            else if ($date_start > $date_until)
                return Response::json('date_start_after_until_date', 400);
        }

        // $years  = [];
        // $months = [];
        // if ($request->filled('year_monts'))
        // {
        //     foreach ($year_monts as $ym)
        //     {
        //         $year   = substr($ym, 0, 4);
        //         $month  = substr($ym, 5, 2);
        //         $ym_date= date("$year-$month-01");

        //         $rst_ym = substr($research->start_date, 0, 7);
        //         $ren_ym = substr($research->end_date, 0, 7);

        //         if ($ym_date >= date("$rst_ym-01") && $ym_date <= date("$ren_ym-01")) // ym is within research
        //         {
        //             $years[]  = $year;
        //             $months[] = $month;
        //         }
        //     }
        // }

        // User specific data
        $research_consents = DB::table('research_user')
                            ->where('research_id', $id)
                            ->whereDate('updated_at', '<=', $date_until)
                            ->orderBy('user_id')
                            ->orderBy('updated_at','asc')
                            ->get();


        $consented_users = DB::table('research_user')
                            ->where('research_id', $id)
                            ->where('consent', 1)
                            ->whereDate('updated_at', '<=', $date_until)
                            ->groupBy('user_id')
                            ->get();

        $data    = [];

        // keep track of doubles
        $loc_ids = [];
        $dev_ids = [];
        $ins_ids = []; 

        foreach ($consented_users as $user_c) 
        {
            $user_id           = $user_c->user_id;
            $user              = User::find($user_id);
            $user_consents     = $research_consents->where('user_id', $user_id);
            $user_consent_obj  = $user_consents->first();
            $user_consent      = $user_consent_obj->consent;

            if ($user_consents->count() == 0 || ($user_consents->count() == 1 && $user_consent === 0)) // if only 1 and consent is false, stop
                continue;

            $date_curr_consent = $user_consent_obj->updated_at;
            $date_consent_start= $date_curr_consent < $date_start ? $date_start : $date_curr_consent; // take latest of two dates
            $date_next_consent = $date_until;

            //die(print_r([$user_consents, $date_curr_consent, $date_next_consent, $index]));

            if ($item == 'measurements' || $item == 'weather')
                $this->cacheRequestRate('get-measurements-research');
            
            // Get all user locations
            $loc_array = [];
            if (isset($user_consent_obj->consent_location_ids) || isset($location_id) || filled($location_ids)) // Set consent based on specific items
            {
                if (isset($user_consent_obj->consent_location_ids))
                {
                    $loc_array = explode(',', $user_consent_obj->consent_location_ids);

                    if (filled($location_ids))
                        $loc_array = array_intersect($loc_array, $location_ids);
                }
                else if (filled($location_ids))
                {
                    $loc_array = $location_ids;
                }

                $user_locations = $user->allLocations()->whereIn('id', $loc_array)
                                                       ->where('created_at', '<', $date_until)
                                                       ->orderBy('created_at')
                                                       ->get();
            }
            else
            {
                $user_locations = $user->allLocations()->where('created_at', '<', $date_until)
                                                       ->orderBy('created_at')
                                                       ->get();
            }
            
            // Get all user devices
            if (isset($user_consent_obj->consent_sensor_ids) || filled($device_ids)) // Set consent based on specific items
            {
                $device_array = [];
                if (isset($user_consent_obj->consent_sensor_ids))
                {
                    $device_array = explode(',', $user_consent_obj->consent_sensor_ids);

                    if (filled($device_ids))
                        $device_array = array_intersect($device_array, $device_ids);
                }
                else if (filled($device_ids))
                {
                    $device_array = $device_ids;
                }

                $user_devices  = $user->devices()->whereIn('id', $device_array)
                                                 ->where('created_at', '<', $date_until)
                                                 ->orderBy('created_at')
                                                 ->get();
            }
            else
            {
                $user_devices  = $user->devices()->where('created_at', '<', $date_until)
                                                 ->orderBy('created_at')
                                                 ->get();
            }
            

            $user_measurements = [];

            // add inspections (also from collaborators)
            $loc_inspection_ids = [];
            foreach ($user_locations as $loc)
            {
                $loc_inspections = $loc->inspections()
                                       ->where('created_at', '>=', $date_consent_start)
                                       ->where('created_at', '<', $date_until)
                                       ->get();
                foreach ($loc_inspections as $ins) 
                    $loc_inspection_ids[] = $ins->id;
                
            }
            $loc_inspections = Inspection::whereIn('id', $loc_inspection_ids)
                                            ->with('items')
                                            ->where('created_at', '>=', $date_consent_start)
                                            ->where('created_at', '<', $date_until)
                                            ->orderBy('created_at')
                                            ->get();
            

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

                // Minimize data to requested dates
                if ($request->filled('date_start') && $date_start > $date_curr_consent)
                {
                    if ($date_start < $date_next_consent)
                        $date_curr_consent = $date_start;
                    else
                        continue; // start >= next_consent, so hide this data from dataset, because earlier than requested
                }
                            
                if ($request->filled('date_until') && $date_until < $date_next_consent)
                {
                    if ($date_until > $date_curr_consent)
                        $date_next_consent = $date_until;
                    else
                        continue; // until <= curr_consent, so hide this data from dataset, because later than requested
                }

                // Fill objects for consent period
                if ($user_consent && ($next_consent || $i == 0))
                {    
                    // add 
                    switch($item)
                    {
                        case 'apiaries':
                        case 'locations':
                            $locs = $user_locations
                                            ->whereNotIn('id', $loc_ids)
                                            ->where('created_at', '<=', $date_next_consent)
                                            ->toArray();
                            // exclude these items from future addition to output 
                            foreach ($locs as $l) 
                                $loc_ids[] = $l['id'];

                            $data = array_merge($data, $locs);
                            break;
                        case 'devices':
                            $devs = $user_devices
                                            ->whereNotIn('id', $dev_ids)
                                            ->where('created_at', '<=', $date_next_consent)
                                            ->toArray();
                            // exclude these items from future addition to output 
                            foreach ($devs as $d) 
                                $dev_ids[] = $d['id'];

                            $data = array_merge($data, $devs);
                            break;
                        case 'inspections':
                            $insp = $loc_inspections
                                            ->whereNotIn('id', $ins_ids)
                                            ->where('created_at', '>=', $date_curr_consent)
                                            ->where('created_at', '<=', $date_next_consent)
                                            ->toArray();
                            // exclude these items from future addition to output 
                            foreach ($insp as $i) 
                                $ins_ids[] = $i['id'];

                            $data = array_merge($data, $insp);
                            break;
                        case 'measurements':
                            // Add measurement data
                            if ($user_devices->count() > 0)
                            {
                                $m_start            = $date_curr_consent;
                                $m_until            = $date_next_consent;
                                $m_interval         = $interval;
                                $filter_year_months = filled($year_months);

                                foreach ($user_devices as $device)
                                {
                                    if ($device)
                                    {
                                        if (($request->filled('device_id') && $request->input('device_id') == $device->id) || !$request->filled('device_id'))
                                        {   
                                            // Allow 'normal' data view by 1 device and interval/index combination as in DeviceController 
                                            if (isset($index) && $data_call)
                                            {
                                                 // limit start/end of interval by start of consent
                                                $staTimestamp = new Moment(null, $timeZone);
                                                $endTimestamp = new Moment(null, $timeZone);

                                                $startOfInt   = $staTimestamp->startOf($m_interval);
                                                $endOfInt     = $endTimestamp->endOf($m_interval);

                                                $now_tst = date($this->timeFormat);

                                                if ($now_tst < $m_until)
                                                    $m_until = $now_tst;

                                                $staMom = new Moment($m_start, $timeZone);
                                                $endMom = new Moment($m_until, $timeZone);

                                                $staSecDiff = $staMom->from($startOfInt)->getSeconds();
                                                $endSecDiff = $endMom->from($endOfInt)->getSeconds();

                                                if ($staSecDiff < 0)
                                                    $startOfInt = $staMom;

                                                if ($endSecDiff < 0)
                                                    $endOfInt = $endMom;

                                                //die(print_r([$m_interval, $m_start, $m_until, $staSecDiff, $endSecDiff]));
                                                // Convert interval to the Influx grouping interval
                                                switch($m_interval){
                                                    case 'year':
                                                        $startOfInt->subtractYears($index);
                                                        $endOfInt->subtractYears($index);
                                                        $m_interval = '1d';
                                                        break;
                                                    case 'month':
                                                        $startOfInt->subtractMonths($index);
                                                        $endOfInt->subtractMonths($index);
                                                        $m_interval = '3h';
                                                        break;
                                                    case 'week':
                                                        $startOfInt->subtractWeeks($index);
                                                        $endOfInt->subtractWeeks($index);
                                                        $m_interval = '1h';
                                                        break;
                                                    case 'day':
                                                        $startOfInt->subtractDays($index);
                                                        $endOfInt->subtractDays($index);
                                                        $m_interval = '10m';
                                                        break;
                                                    case 'hour':
                                                    case 'live':
                                                        $startOfInt->subtractHours($index);
                                                        $endOfInt->subtractHours($index);
                                                        $m_interval = '1m';
                                                        break;
                                                    case 'research':
                                                        $startOfInt = new Moment($m_start, 'UTC');
                                                        $endOfInt = new Moment($m_until, 'UTC');
                                                        $m_interval = '1d';
                                                        break;
                                                }
                                                
                                                // set index based time
                                                $m_start = $startOfInt->setTimezone('UTC')->format($this->timeFormat);
                                                $m_until = $endOfInt->setTimezone('UTC')->format($this->timeFormat);
                                                //die(print_r([$m_interval, $m_start, $m_until]));
                                            }

                                            $adds = $calculation == 'WEEKMAP' ? [] : ['device_id'=>$device->id];
                                            $where= $device->influxWhereKeys().' AND time >= \''.$m_start.'\' AND time <= \''.$m_until.'\'';
                                            
                                            if ($csv_output)
                                            {
                                                // 2021-03-30_T1UK_HPP 8a.csv
                                                $file_name = $research->name.'/'.substr($m_start, 0, 10).'/'.$device->location_name.$device->hive_name.'.csv';
                                                $file_path = $this->exportCsvFromInflux($where, $file_name, $measurements, 'sensors');
                                                $data[]    = $file_path;
                                            }
                                            else
                                            {
                                                $meas = $this->getArrayFromInflux($where, $measurements, 'sensors', $m_interval, $calculation, $calc_prop, $decimals, $precision, $adds, $limit, $device, $timeZone, $m_start, $m_until); // $where, $measurements='*', $database='sensors', $interval='1h', $calculation='MEAN', $calc_prop=null, $decimals=2, $precision='rfc3339', $adds=[], $limit=5000)

                                                // filter out all non month-year data
                                                if ($filter_year_months)
                                                {
                                                    $meas_filtered = [];
                                                    foreach ($meas as $m)
                                                    {
                                                        $time       = $m['time'];
                                                        $year_month = substr($time, 0, 7); // TODO: Make local time, now UTC

                                                        if (in_array($year_month, $year_months))
                                                            $meas_filtered[] = $m;
                                                    }
                                                    $meas = $meas_filtered;
                                                }

                                                // merge data with already available
                                                if ($calculation == 'WEEKMAP' && count($data) > 0) // only merge weekmaps if data is already filled
                                                {
                                                    // weekmap data: [measurement][day][hour] = value
                                                    $wm_add = false;
                                                    foreach ($meas as $wd_m => $wd_array)
                                                    {
                                                        foreach ($wd_array as $wd => $wd_val)
                                                        {
                                                            if (gettype($wd_val) == 'array' && count($wd_val) > 0) // weekday array, $wd = weekday name
                                                            {
                                                                foreach ($wd_val as $wd_h => $wd_h_ave)
                                                                {
                                                                    if (isset($data[$wd_m][$wd][$wd_h]))
                                                                        $data[$wd_m][$wd][$wd_h] = ($data[$wd_m][$wd][$wd_h] + $wd_h_ave) / 2; // TODO: solve wrong average calculation for >2 values
                                                                    else
                                                                        $data[$wd_m][$wd][$wd_h] = $wd_h_ave; // add unexisting weekhour value

                                                                    $wm_add = true;
                                                                }
                                                            }
                                                        }
                                                    }
                                                    if ($wm_add)
                                                    {
                                                        $wm_device_ids = [$device->id];
                                                        
                                                        if (isset($data['device_ids']))
                                                            $wm_device_ids = array_merge($wm_device_ids, explode(',', $data['device_ids']));

                                                        sort($wm_device_ids);
                                                        $data['device_ids'] = implode(',', $wm_device_ids);
                                                    }
                                                }
                                                else // no WEEKMAP, or first data of WEEKMAP
                                                {
                                                    $data = array_merge($data, $meas);

                                                    if ($calculation == 'WEEKMAP' && count($data) > 0) // set the first id's of the WEEKMAP data
                                                    {
                                                        $data['device_ids'] = $device->id;
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    // Add weather data
                                    $loc = $device->location();
                                    if ($loc && isset($loc->created_at) && $loc->created_at <= $m_until && $loc->lat != '' && $loc->lon != '')
                                    {
                                        $where  = '"lat" = \''.$loc->lat.'\' AND "lon" = \''.$loc->lon.'\' AND time >= \''.$m_start.'\' AND time <= \''.$m_until.'\'';
                                        
                                        if ($csv_output)
                                        {
                                            // 2021-03-30_T1UK_HPP 8a.csv
                                            $file_name = substr($m_start, 0, 10).'_'.$research->name.'_'.$device->location_name.$device->hive_name;
                                            $file_path = $this->exportCsvFromInflux($where, $file_name, $measurements, 'weather');
                                            $data[]    = $file_path;
                                        }
                                        else
                                        {
                                            $adds   = ['device_id'=>$device->id]; // provide anonimized (10-15km diameter) for end result
                                            $data_w = $this->getArrayFromInflux($where, $measurements, 'weather', $m_interval, $calculation, $calc_prop, $decimals, $precision, $adds, $limit);

                                            // Merge measurements with weather data 
                                            if (count($data_w) > 0)
                                            {
                                                if ($m_interval == '*' || count($data) == 0 || $m_interval == 'research')
                                                {
                                                    
                                                    if ($filter_year_months) // filter out all non month-year data
                                                    {
                                                        $meas_filtered = [];
                                                        foreach ($data_w as $m)
                                                        {
                                                            $time       = $m['time'];
                                                            $year_month = substr($time, 0, 7); // TODO: Make local time, now UTC
                                                            if (in_array($year_month, $year_months))
                                                                $meas_filtered[] = $m;
                                                        }
                                                        $data_w = $meas_filtered;
                                                    }
                                                    $data = array_merge($data, $data_w);
                                                }
                                                else // merge neatly in same time objects
                                                {
                                                    $weather_time_key = [];
                                                    foreach ($data_w as $values) 
                                                    {
                                                        $time = $values['time'];
                                                        if ($filter_year_months) // filter out all non month-year data
                                                        {
                                                            $year_month  = substr($time, 0, 7); // TODO: Make local time, now UTC
                                                            if (in_array($year_month, $year_months))
                                                                $weather_time_key[$time] = $values;
                                                        }
                                                        else
                                                        {
                                                            $weather_time_key[$time] = $values;
                                                        }
                                                    }                

                                                    // add weather values to sensor time keys where the weather values also exist
                                                    if (filled($weather_time_key))
                                                    {
                                                        foreach ($data as $i => $values)
                                                        {
                                                            $time = $values['time'];
                                                            if (isset($weather_time_key[$time])) // add weather data to already available datetime
                                                            {
                                                                foreach ($weather_time_key[$time] as $m => $v)
                                                                    $data[$i][$m] = $v;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            break;
                        default:
                            return Response::json('invalid_item', 400);
                    }
                }
            }
        }

        // Check if the mean values need to be added 
        if ($data_call && $csv_output == false)
        {
            $data_count = count($data);
            
            if ($data_count > 0)
            {

                $measurements        = $data;

                $data                = [];
                $data['start']       = $m_start;
                $data['end']         = $m_until;
                $data['measurements']= $measurements;
                $data['interval']    = $interval;
                $data['index']       = $index;
                $data['timeZone']    = $timeZone;

                $mean_values         = [];
                // set mean values time to last timestamp
                $mean_values['time'] = isset($measurements[$data_count-1]['time']) ? $measurements[$data_count-1]['time'] : null; 
                
                for ($i=0; $i < $data_count; $i++)
                {
                    // Fill mean values
                    $data_array = $measurements[$i]; 
                    foreach ($data_array as $name => $value) 
                    {
                        if (isset($value) && $name != 'time')
                        {
                            if (!isset($mean_values[$name]))
                                $mean_values[$name] = [$value];
                            else
                                $mean_values[$name][] = $value;
                        }
                    }
                }
                // Calculate mean values
                foreach ($mean_values as $name => $values) 
                {
                    if ($name != 'time' && count($values) > 0)
                    {
                        $mean_values[$name.'_min'] = min($values);
                        $mean_values[$name.'_max'] = max($values);
                        $mean_values[$name]        = array_sum($values) / count($values);
                    }
                }
                $data['mean_values'] = $this->round_values_in_array($mean_values);
            }
        }

        return Response::json($data);
    }

    private function round_values_in_array($value_array)
    {
        $names_not_round = ['time', 'start', 'end', 'from_cache', 'file_type', 'bv'];
        
        foreach ($value_array as $name => $value)
        {
            if (!in_array($name, $names_not_round) && is_numeric($value))
            {
                $float_value        = floatval($value);
                $decimals           = $float_value > 100 ? 0 : 1;
                $value_array[$name] = round($float_value, $decimals);
            }
        }
        return $value_array;
    }


    private function validateDate($date, $format='Y-m-d H:i:s') // RFC3339 == 'Y-m-d\TH:i:sP'
    {
        $unix  = strtotime($date);
        return date($format, $unix) === $date ? true : false;
    }

    private function influx_fields_to_query_string($fields, $calculation='NONE', $calc_prop=null, $decimals=2)
    {
        $round_factor = $decimals === null ? 0 : pow(10, $decimals);

        foreach ($fields as $i => $name)
        {
            if ($calculation == 'NONE' || empty($calculation)) // no calculations
            {
                if ($round_factor === 0)
                    $fields[$i] = '"'.$name.'"';
                else
                    $fields[$i] = 'ROUND(("'.$name.'")*'.$round_factor.')/'.$round_factor.' AS "'.$name.'"';
            }
            else
            {
                $calculation         = strtoupper($calculation);
                $calc_prop_add       = '';

                // BOXPLOT: Min, Max, Mean, Percentile 25, Percentile 75
                if ($calculation == 'BOXPLOT')
                {
                    $fields_sub   = [];
                    $fields_sub[] = 'MIN("'.$name.'") AS "'.$name.'_min"';
                    $fields_sub[] = 'MAX("'.$name.'") AS "'.$name.'_max"';
                    $fields_sub[] = 'MEDIAN("'.$name.'") AS "'.$name.'_med"';
                    $fields_sub[] = 'COUNT("'.$name.'") AS "'.$name.'_cnt"';
                    $fields_sub[] = 'MEAN("'.$name.'") AS "'.$name.'"';
                    $fields_sub[] = 'PERCENTILE("'.$name.'", 1) AS "'.$name.'_p01"';
                    $fields_sub[] = 'PERCENTILE("'.$name.'", 25) AS "'.$name.'_p25"';
                    $fields_sub[] = 'PERCENTILE("'.$name.'", 75) AS "'.$name.'_p75"';
                    $fields_sub[] = 'PERCENTILE("'.$name.'", 99) AS "'.$name.'_p99"';
                    $fields[$i]   = implode(', ', $fields_sub);
                }
                else if ($calculation == 'PEAKS')
                {
                    //SELECT ((SPREAD("pmsp053_pm10") / MODE("pmsp053_pm10")) + ABS(SPREAD("pmsp053_pm10") / MODE("pmsp053_pm10"))) / 2 AS "Peak PM10" FROM "sensors" WHERE ("key" =~ /^$sensor_key$/) AND $timeFilter GROUP BY time(6h) fill(null)
                    $fields[$i] = '(((SPREAD("'.$name.'") / MODE("'.$name.'"))) + ABS((SPREAD("'.$name.'") / MODE("'.$name.'")))) / 2 AS "'.$name.'"';
                }
                else
                {
                    // DERIVATIVE no property exception
                    if (isset($calc_prop))
                    {
                        $calc_prop_add = ", $calc_prop";
                    }
                    else if ($calculation == 'DERIVATIVE') // no calc_prop set (time of derivative)
                    {
                        $calculation   = 'DERIVATIVE(MEAN';
                        $calc_prop_add = ')'; // add extra bracket for MEAN
                    }

                    // Make query
                    if ($round_factor === 0)
                        $fields[$i] = $calculation.'("'.$name.'"'.$calc_prop_add.') AS "'.$name.'"';
                    else
                        $fields[$i] = 'ROUND('.$calculation.'("'.$name.'"'.$calc_prop_add.')*'.$round_factor.')/'.$round_factor.' AS "'.$name.'"';
                }
            }
        }
        return implode(', ', $fields);
    }

    // NB: keys should be filled with keys array for database 'sensors', or with coords ['lat'=>$loc->lat, 'lon'=>$loc->lon] for database 'weather'
    private function getArrayFromInflux($where, $measurements='*', $database='sensors', $interval='1h', $calculation='MEAN', $calc_prop=null, $decimals=2, $precision='rfc3339', $adds=[], $limit=5000, $device=null, $timeZone='UTC', $start_date=null, $end_date=null)
    {
        $options = ['precision'=>$precision];
        
        // Create query
        $names = null;
        $replace_names   = [];
        $replace_results = [];

        if (isset($measurements) && $measurements !== '*')
        {
            if (gettype($measurements) == 'array' && count($measurements) > 0)
            {
                foreach($replace_names as $m_source => $m_target)
                {
                    if (in_array($m_target, $measurements) || in_array($m_target, $measurements)) // change pmsp053_pm1.0 to pmsp053_pm1_0
                    {
                        $replace_results[$m_source] = $m_target;
                        if (!in_array($m_source, $measurements))
                            $measurements[] = $m_source; // also query measurements that should be replaced
                    }
                }
                $names = $measurements;
            }
            else if (gettype($measurements) == 'string')
            {
                $names = explode(',', $measurements);
            }
        }
        else
        {
            $replace_results = $replace_names;
        }

        // add extra measurements to replace
        if (count($replace_results) > 0 && $calculation == 'BOXPLOT')
        {
            $add_suffix = ['_min','_max','_med','_cnt','_p01','_p25','_p75','_p99'];
            foreach($replace_results as $m_source => $m_target)
            {
                foreach($add_suffix as $s)
                    $replace_results[$m_source.$s] = $m_target.$s;
            }
        }

        // Initialize names if not available
        if (!isset($names))
        {
            if ($database == 'weather')
                $names = $this->output_weather;
            else
                $names = $this->output_sensors;
        }
        
        // Create query
        $queryList = [];
        if ($database != 'weather' && $calculation != 'NETWEIGHT')
        {
            $queryList = Device::getAvailableSensorNamesNoCache($names, $where, $database); // ($names, $where, $table='sensors', $output_sensors_only=true, $cache_name='names-nocache')
        }
        
        if (!isset($queryList) || gettype($queryList) != 'array' || count($queryList) == 0)
            $queryList = $names;

        if (isset($interval) && $calculation == 'WEEKMAP') // create hourly weekmap
        {
            $groupBySelect     = $this->influx_fields_to_query_string($queryList, 'MEAN', $calc_prop, $decimals);
            $groupByResolution = 'GROUP BY time(1h) fill(none) ORDER BY time ASC';
        }
        else if (isset($interval) && $interval == '*' && $calculation != 'NONE') // Set full consent interval and possible calculation (MEAN/MIN/MAX/etc), but do not group by time
        {
            $groupBySelect     = $this->influx_fields_to_query_string($queryList, $calculation, $calc_prop, $decimals);
            $groupByResolution = 'ORDER BY time ASC';
        }
        else if (isset($interval) && $interval !== '*' && $calculation != 'NONE') // Set interval and possible calculation (MEAN/MIN/MAX/etc), 
        {
            $groupBySelect     = $this->influx_fields_to_query_string($queryList, $calculation, $calc_prop, $decimals);
            $groupByResolution = 'GROUP BY time('.$interval.') fill(none) ORDER BY time ASC';
        }
        else // $interval == '*' or unset || $calculation == 'NONE'
        {
            $groupBySelect     = $this->influx_fields_to_query_string($queryList);
            $groupByResolution = 'ORDER BY time ASC';
        }
        
        if ($calculation == 'NETWEIGHT' && $device && $start_date && $end_date && $timeZone)
        {
            $query = $device->getCleanedWeightQuery($interval, $start_date, $end_date, $limit, 0.75, 2, $timeZone);
            //dd($query);
        }
        else
        {
            $query = 'SELECT '.$groupBySelect.' FROM "'.$database.'" WHERE '.$where.' '.$groupByResolution.' LIMIT '.$limit;
        }
        
        //dd($query);

        if ($query === null)
            return [];

        // Load data
        $cache_timeout_sec = 84600; // 24 hours, was env('CACHE_TIMEOUT_LONG')
        $out = Cache::remember('research-query-'.$query, $cache_timeout_sec, function () use ($query, $queryList, $options, $adds, $replace_results, $calculation)
        { 
            $data     = [];
            $adds_set = count($adds) > 0 ? true : false;

            try{
                $this->cacheRequestRate('influx-get');
                $this->cacheRequestRate('influx-research-api');
                //die(print_r($query));
                $data = $this->client::query($query, $options)->getPoints();
            } catch (InfluxDB\Exception $e) {
                Log::error($query);
                Log::error($e);
                return [];
            }
            

            if (count($data) == 0)
            {
                return [];
            }
            else
            {
                // Prepare WEEKMAP measurement data
                if ($calculation == 'WEEKMAP')
                {
                    // fill week 
                    $weekmap   = [];
                    $data_week = [];
                    for ($day_i=0; $day_i < 7; $day_i++)
                    { 
                        $data_week[$day_i] = [];
                        for ($hour_i=0; $hour_i < 24; $hour_i++)
                        { 
                            $data_week[$day_i][$hour_i] = [];
                        }
                    }
                    // fill names with week arrays
                    foreach ($queryList as $m_abbr)
                    {
                        if (in_array($m_abbr, array_keys($replace_results)))
                            $m_abbr = $replace_results[$m_abbr];

                        $weekmap[$m_abbr] = $data_week;
                    }
                }

                // Process measurement data
                foreach ($data as $i => $d)
                {
                    // Replace measurement names
                    // Empty null results
                    $data[$i] = array_filter($d, function($value) { return !is_null($value) && $value !== ''; });
                    
                    // Convert to WEEKMAP (heatmap of all hours in a week)
                    if ($calculation == 'WEEKMAP')
                    {
                        if (isset($data[$i]['time']) && count($data[$i]) > 1)
                        {
                            $time    = strtotime($data[$i]['time']);
                            $weekday = date('N', $time)-1; // 1-7 -> 0-6
                            $dayhour = date('G', $time); // 0-23

                            foreach ($data[$i] as $m_abbr => $value)
                            {
                                if ($m_abbr != 'time')
                                    $weekmap[$m_abbr][$weekday][$dayhour][] = $value;
                            }
                        }
                    }
                    else
                    {
                        // Add items in adds
                        if ($adds_set)
                            foreach ($adds as $add_key => $add_value)
                                $data[$i][$add_key] = $add_value;
                    }


                }

                // Post process WEEKMAP
                if ($calculation == 'WEEKMAP')
                {
                    $weekmap_out = [];
                    $weekdays    = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
                    foreach ($weekmap as $m_abbr => $week_array)
                    {
                        foreach($week_array as $day_i => $day_array)
                        { 
                            foreach($day_array as $hour_i => $hour_array)
                            { 
                                if (count($hour_array) > 0)
                                    $weekmap_out[$m_abbr][$weekdays[$day_i]][$hour_i] = array_sum($hour_array) / count($hour_array);
                            }
                        }
                        // Add items in adds
                        if ($adds_set)
                            foreach ($adds as $add_key => $add_value)
                                $weekmap_out[$m_abbr][$add_key] = $add_value;
                    }
                    $data = $weekmap_out;
                }
            }

            return $data;
        });

        return $out;
    }


    private function exportCsvFromInflux($where, $fileName='research-export-', $measurements='*', $database='sensors', $separator=',', $translate=false)
    {
        $options= ['precision'=>'rfc3339', 'format'=>'csv'];
        
        if ($database == 'sensors')
        {
            if (isset($measurements) && gettype($measurements) == 'array' && count($measurements) > 0)
                $names = $measurements;
            else
                $names = $this->output_sensors;

            $queryList = Device::getAvailableSensorNamesNoCache($names, $where, $database);
            
            if (isset($queryList) && gettype($queryList) == 'array' && count($queryList) > 0)
                $groupBySelect = implode(', ', $queryList);
            else 
                $groupBySelect = '"'.implode('","',$names).'"';

            $query = 'SELECT '.$groupBySelect.',"from_flashlog" FROM "'.$database.'" WHERE '.$where;
        }
        else // i.e. weather data
        {
            if ($measurements == null || $measurements == '' || $measurements === '*')
                $sensor_measurements = '*';
            else
                $sensor_measurements = $measurements;

            $query = 'SELECT '.$sensor_measurements.' FROM "'.$database.'" WHERE '.$where;
        }
        
        try{
            $data   = $this->client::query($query, $options)->getPoints(); // get first sensor date
        } catch (InfluxDB\Exception $e) {
            return null;
        }

        if (count($data) == 0)
            return null;

        $csv_file = $data;

        //format CSV header row: time, sensor1 (unit2), sensor2 (unit2), etc. Excluse the 'sensor' and 'key' columns
        $csv_file = "";

        $csv_sens = array_keys($data[0]);
        $csv_head = [];
        if ($translate)
        {
            foreach ($csv_sens as $sensor_name) 
            {
                if ($sensor_name == 'from_flashlog')
                {
                    $csv_head[] = 'Imported from device flash log';
                }
                else
                {
                    $meas       = Measurement::where('abbreviation', $sensor_name)->first();
                    $csv_head[] = $meas ? $meas->pq_name_unit().' ('.$sensor_name.')' : $sensor_name;
                }
            }
        } 
        else
        {
            $csv_head = $csv_sens;
        } 
        $csv_head = '"'.implode('"'.$separator.'"', $csv_head).'"'."\r\n";

        // format CSV file body
        $csv_body = [];
        foreach ($data as $sensor_array) 
        {
            if (isset($sensor_array['time']))
            {
                $time_str = $sensor_array['time'];
                $sensor_array['time'] = substr($time_str, 0, 10).' '.substr($time_str, 11, 8); // Format from 2024-10-16T03:17:14Z => YYYY-MM-DD HH:mm:ss
            }

            $csv_body[] = implode($separator, $sensor_array);
        }
        $csv_file = $csv_head.implode("\r\n", $csv_body);

        // return the CSV file content in a file on disk
        $filePath = 'exports/'.$fileName;
        $disk     = env('EXPORT_STORAGE', 'public');

        if (Storage::disk($disk)->put($filePath, $csv_file, ['mimetype' => 'text/csv']))
            return Storage::disk($disk)->url($filePath);

        return null;
    }

}
