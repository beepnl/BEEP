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
use App\Measurement;
use App\Models\FlashLog;
use Moment\Moment;
use Response;
use DB;
use Cache;

/**
 * @group Api\ResearchDataController
 * Retreive owned or viewable Research data
 */
class ResearchDataController extends Controller
{
    
    protected $valid_sensors  = [];
    protected $output_sensors = [];

    public function __construct()
    {
        $this->valid_sensors  = Measurement::getValidMeasurements();
        $this->output_sensors = Measurement::getValidMeasurements(true);
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
    List all user 'item' data within the consent=1 periods of a specific user within a Research. The 'item' field indicates the type of user data (apiaries/hives/devices/flashlogs/inspections/measurements/weather) to request within the research (which the user gave consent for to use). Example: inspectionsResponse: api/researchdata/1/user/1/inspections. 
    @authenticated
    @urlParam id required The research ID to request data from. Example: 1
    @urlParam user_id required The user id to request data from. Example: 1
    @urlParam item required The type of user data (apiaries/hives/devices/inspections/measurements) to request within the research (which the user gave consent for to use). Example: inspections
    @bodyParam date_start datetime The date in 'YYYY-MM-DD HH:mm:ss' format (2020-01-01 00:00:00) to request data from (default is beginning of research, or earlier (except inspections and measurements). Example: 2020-01-01 00:00:00
    @bodyParam date_until datetime The date in 'YYYY-MM-DD HH:mm:ss' format (2020-09-29 23:59:59) to request data until (default is until the end of the user consent, or research end). Example: 2020-09-29 23:59:59
    @bodyParam device_id integer The device_id to filter the measurements on (next to date_start and date_until). Example: 1
    @bodyParam precision string Specifies the optional InfluxDB format/precision (rfc3339/h/m/s/ms/u) of the timestamp of the measurements and weather data: rfc3339 (YYYY-MM-DDTHH:MM:SS.nnnnnnnnnZ), h (hours), m (minutes), s (seconds), ms (milliseconds), u (microseconds). Precision defaults to rfc3339. Example: rfc3339
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
                },
                {
                    "id": 326541,
                    "value": "4",
                    "inspection_id": 40162,
                    "category_id": 980,
                    "val": "4",
                    "unit": "x 25cm2",
                    "type": "square_25cm2"
                },
                {
                    "id": 326542,
                    "value": "3",
                    "inspection_id": 40162,
                    "category_id": 981,
                    "val": "3",
                    "unit": "x 25cm2",
                    "type": "square_25cm2"
                },
                {
                    "id": 326543,
                    "value": "581",
                    "inspection_id": 40162,
                    "category_id": 982,
                    "val": "581",
                    "unit": "bzz",
                    "type": "number_positive"
                },
                {
                    "id": 326544,
                    "value": "5",
                    "inspection_id": 40162,
                    "category_id": 984,
                    "val": "5",
                    "unit": "x 25cm2",
                    "type": "square_25cm2"
                },
                {
                    "id": 326545,
                    "value": "1",
                    "inspection_id": 40162,
                    "category_id": 985,
                    "val": "1",
                    "unit": "x 25cm2",
                    "type": "square_25cm2"
                },
                {
                    "id": 326546,
                    "value": "4",
                    "inspection_id": 40162,
                    "category_id": 987,
                    "val": "4",
                    "unit": "x 25cm2",
                    "type": "square_25cm2"
                },
                {
                    "id": 326547,
                    "value": "5",
                    "inspection_id": 40162,
                    "category_id": 988,
                    "val": "5",
                    "unit": "x 25cm2",
                    "type": "square_25cm2"
                },
                {
                    "id": 326548,
                    "value": "4",
                    "inspection_id": 40162,
                    "category_id": 989,
                    "val": "4",
                    "unit": "x 25cm2",
                    "type": "square_25cm2"
                },
                {
                    "id": 326549,
                    "value": "2",
                    "inspection_id": 40162,
                    "category_id": 990,
                    "val": "2",
                    "unit": "x 25cm2",
                    "type": "square_25cm2"
                },
                {
                    "id": 326550,
                    "value": "2",
                    "inspection_id": 40162,
                    "category_id": 991,
                    "val": "2",
                    "unit": "x 25cm2",
                    "type": "square_25cm2"
                },
                {
                    "id": 326551,
                    "value": "3",
                    "inspection_id": 40162,
                    "category_id": 992,
                    "val": "3",
                    "unit": "x 25cm2",
                    "type": "square_25cm2"
                },
                {
                    "id": 326552,
                    "value": "3",
                    "inspection_id": 40162,
                    "category_id": 993,
                    "val": "3",
                    "unit": "x 25cm2",
                    "type": "square_25cm2"
                },
                {
                    "id": 326553,
                    "value": "6",
                    "inspection_id": 40162,
                    "category_id": 995,
                    "val": "6",
                    "unit": "x 25cm2",
                    "type": "square_25cm2"
                },
                {
                    "id": 326554,
                    "value": "2",
                    "inspection_id": 40162,
                    "category_id": 996,
                    "val": "2",
                    "unit": "x 25cm2",
                    "type": "square_25cm2"
                },
                {
                    "id": 326555,
                    "value": "2",
                    "inspection_id": 40162,
                    "category_id": 997,
                    "val": "2",
                    "unit": "x 25cm2",
                    "type": "square_25cm2"
                },
                {
                    "id": 326556,
                    "value": "2",
                    "inspection_id": 40162,
                    "category_id": 998,
                    "val": "2",
                    "unit": "x 25cm2",
                    "type": "square_25cm2"
                },
                {
                    "id": 326557,
                    "value": "2",
                    "inspection_id": 40162,
                    "category_id": 999,
                    "val": "2",
                    "unit": "x 25cm2",
                    "type": "square_25cm2"
                },
                {
                    "id": 326558,
                    "value": "2",
                    "inspection_id": 40162,
                    "category_id": 1000,
                    "val": "2",
                    "unit": "x 25cm2",
                    "type": "square_25cm2"
                },
                {
                    "id": 326559,
                    "value": "2",
                    "inspection_id": 40162,
                    "category_id": 1001,
                    "val": "2",
                    "unit": "x 25cm2",
                    "type": "square_25cm2"
                },
                {
                    "id": 326560,
                    "value": "8",
                    "inspection_id": 40162,
                    "category_id": 1163,
                    "val": "8",
                    "unit": "x 25cm2",
                    "type": "square_25cm2"
                },
                {
                    "id": 326561,
                    "value": "4",
                    "inspection_id": 40162,
                    "category_id": 1164,
                    "val": "4",
                    "unit": "x 25cm2",
                    "type": "square_25cm2"
                },
                {
                    "id": 326562,
                    "value": "2",
                    "inspection_id": 40162,
                    "category_id": 1165,
                    "val": "2",
                    "unit": "x 25cm2",
                    "type": "square_25cm2"
                },
                {
                    "id": 326563,
                    "value": "6",
                    "inspection_id": 40162,
                    "category_id": 1166,
                    "val": "6",
                    "unit": "x 25cm2",
                    "type": "square_25cm2"
                },
                {
                    "id": 326564,
                    "value": "2",
                    "inspection_id": 40162,
                    "category_id": 1167,
                    "val": "2",
                    "unit": "x 25cm2",
                    "type": "square_25cm2"
                },
                {
                    "id": 326565,
                    "value": "2",
                    "inspection_id": 40162,
                    "category_id": 1168,
                    "val": "2",
                    "unit": "x 25cm2",
                    "type": "square_25cm2"
                },
                {
                    "id": 326566,
                    "value": "3",
                    "inspection_id": 40162,
                    "category_id": 1169,
                    "val": "3",
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

        // Check if user is present on 
        if (DB::table('research_user')->where('research_id', $id)->where('user_id', $user_id)->where('consent', 1)->count() == 0)
            return Response::json('user-not-connected-to-research', 400);


        // Make dates
        $research   = Research::findOrFail($id);
        $date_start = $request->input('date_start', $research->start_date);
        $date_until = $request->input('date_until', $research->end_date);
        $precision  = $request->input('precision', 'rfc3339');
        $date_format='Y-m-d H:i:s'; // RFC3339 == 'Y-m-d\TH:i:sP'
            
        if ($request->filled('date_start'))
        {
            if ($this->validateDate($date_start, $date_format) == false)
                return Response::json(['date_start_invalid'=>$date_start, 'format'=>$date_format], 400);
            else if ($date_start < $research->start_date)
                return Response::json('date_start_before_research_start', 400);
        }

        if ($request->filled('date_until'))
        {
            if ($this->validateDate($date_until, $date_format) == false)
                return Response::json(['date_until_invalid'=>$date_until, 'format'=>$date_format], 400);
            else if ($date_until > $research->end_date)
                return Response::json('date_until_after_research_end', 400);
            else if ($date_until < $date_start)
                return Response::json('date_until_after_start_date', 400);
            else if ($date_start > $date_until)
                return Response::json('date_start_after_until_date', 400);
        }

        // User specific data
        $user_consents     = DB::table('research_user')->where('research_id', $id)->where('user_id', $user_id)->whereDate('updated_at', '<', $date_until)->orderBy('updated_at','asc')->get()->toArray();
        $user_consent      = $user_consents[0]->consent;
        $date_curr_consent = $user_consents[0]->updated_at;
        $date_next_consent = $date_until;

        if (count($user_consents) == 0 || (count($user_consents) == 1 && $user_consent === 0)) // if only 1 and consent is false, stop
            return Response::json('user-gave-no-consent', 400);
        //die(print_r([$user_consents, $date_curr_consent, $date_next_consent, $index]));

        if ($item == 'measurements' || $item == 'weather')
            $this->cacheRequestRate('get-measurements-research');
        
        // Get all user data
        $user_apiaries     = Location::withTrashed()->where('user_id', $user_id)->where('created_at', '<', $date_until)->orderBy('created_at')->get();
        $user_hives        = Hive::withTrashed()->where('user_id', $user_id)->where('created_at', '<', $date_until)->orderBy('created_at')->get();
        $user_devices      = Device::withTrashed()->with('sensorDefinitions')->where('user_id', $user_id)->where('created_at', '<', $date_until)->orderBy('created_at')->get();
        $user_flashlogs    = FlashLog::where('user_id', $user_id)->where('created_at', '<', $date_until)->orderBy('created_at')->get();
        $user_measurements = [];

        // add hive inspections (also from collaborators)
        $hive_inspection_ids = [];
        foreach ($user_hives as $hive)
        {
            $hive_inspections = $hive->inspections()->where('created_at', '>=', $date_start)->where('created_at', '<', $date_until)->get();
            foreach ($hive_inspections as $ins) 
                $hive_inspection_ids[] = $ins->id;
            
        }
        $hive_inspections = Inspection::whereIn('id', $hive_inspection_ids)->with('items')->where('created_at', '>=', $date_start)->where('created_at', '<', $date_until)->orderBy('created_at')->get();

        
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
                                        $data = array_merge($data, $this->getArrayFromInflux($where, '*', 'sensors', $precision));
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
                                    $data = array_merge($data, $this->getArrayFromInflux($where, '*', 'weather', $precision));
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

    private function validateDate($date, $format='Y-m-d H:i:s') // RFC3339 == 'Y-m-d\TH:i:sP'
    {
        $unix  = strtotime($date);
        return date($format, $unix) === $date ? true : false;
    }

    private function getArrayFromInflux($where, $measurements='*', $database='sensors', $precision='rfc3339')
    {
        $options = ['precision'=>$precision];
        
        if ($database == 'sensors')
        {
            if (isset($measurements) && gettype($measurements) == 'array' && count($measurements) > 0)
                $names = $measurements;
            else
                $names = $this->output_sensors;
            
            $queryList = Device::getAvailableSensorNamesNoCache($names, $where); // ($names, $table, $where, $limit='', $output_sensors_only=true)
            
            if (isset($queryList) && gettype($queryList) == 'array' && count($queryList) > 0)
                $groupBySelect = implode(', ', $queryList);
            else 
                $groupBySelect = '"'.implode('","',$names).'"';

            $query = 'SELECT "key",'.$groupBySelect.' FROM "'.$database.'" WHERE '.$where;
        }
        else // i.e. weather data
        {
            if ($measurements == null || $measurements == '' || $measurements === '*')
                $sensor_measurements = '*';
            else
                $sensor_measurements = $measurements;

            $query = 'SELECT '.$sensor_measurements.' FROM "'.$database.'" WHERE '.$where;
        }

        $data  = [];

        try{
            $this->cacheRequestRate('influx-get');
            $this->cacheRequestRate('influx-research-api');
            $data = $this->client::query($query, $options)->getPoints(); // get first sensor date
        } catch (InfluxDB\Exception $e) {
            // do nothing
        }
        
        if (count($data) == 0)
            return [];

        return $data;
    }

}
