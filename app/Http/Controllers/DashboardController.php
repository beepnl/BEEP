<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Location;
use App\Category;
use App\Hive;
use App\Inspection;
use App\InspectionItem;
use App\Queen;
use App\Checklist;
use App\ChecklistCategory;
use App\HiveLayerFrame;
use App\Device;
use App\Measurement;
use App\Research;
use App\Models\Alert;
use App\Models\AlertRule;
use DB;

use Illuminate\Support\Facades\Cache;

use Moment\Moment;

class DashboardController extends Controller
{
    public function __construct()
    {
        
    }

    private function cacheRequestGetRate($name, $average_sec=60, $decimals=0)
    {
        if (Cache::has($name.'-time') && Cache::has($name.'-count'))
        {
            $sec_ago     = time() - Cache::get($name.'-time');
            $req_per_min = round(Cache::get($name.'-count') * $average_sec / $sec_ago, $decimals);
            return $req_per_min;
        }
        else
        {
            return 0;
        }
    }

    private function cacheRequestGetArray($name)
    {
        if (Cache::has($name))
        {
            $array = Cache::get($name);
            if (gettype($array) == 'array')
                return implode("\x0A", $array);
            else
                return $array;
        }
        return '';
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $moment             = new Moment();
        $last_hour          = $moment->subtractHours(1)->format('Y-m-d H:i:s');
        $last_day           = $moment->subtractDays(1)->format('Y-m-d H:i:s');
        $last_week          = $moment->subtractDays(7)->format('Y-m-d H:i:s');
        $last_month         = $moment->subtractMonths(1)->format('Y-m-d');
        $last_qrt           = $moment->subtractMonths(3)->format('Y-m-d');
        $last_year          = $moment->subtractMonths(12)->format('Y-m-d');
        //die(print_r($last_day));

        $data                                 = [];
        $data['users']                        = User::count();
        $data['users-locale']                 = User::where('locale', '!=', null)->count();
        $data['newusers']                     = User::where('created_at', '>', $last_week)->count();
        $data['hourusers']                    = User::where('last_login', '>', $last_hour)->count();
        $data['dayusers']                     = User::where('last_login', '>', $last_day)->count();
        $data['activeusers']                  = User::whereDate('last_login', '>', $last_month)->count();

        $users_last_qrt                       = User::whereDate('last_login', '>', $last_qrt);
        $data['qrtusers']                     = $users_last_qrt->count();

        // Active users query:
        $data['qrtusers_more_5_hives']        = count(DB::select(DB::raw('SELECT hives.`user_id`, COUNT(hives.`id`) as hive_cnt, users.`created_at`, users.`last_login` FROM hives 
                                                        INNER JOIN users ON hives.`user_id` = users.`id`
                                                        WHERE hives.`deleted_at` IS NULL AND users.`created_at` != users.`updated_at` AND users.`last_login` > \''.$last_qrt.'\'
                                                        GROUP BY hives.`user_id` HAVING hive_cnt > 5')));
             
        $data['yearusers']                    = User::whereDate('last_login', '>', $last_year)->count();
        $data['locations']                    = Location::count();
        $data['hives']                        = Hive::count();
        $data['frames']                       = HiveLayerFrame::count();
        $data['inspections']                  = Inspection::count();
        $data['inspectionitems']              = InspectionItem::count();
        $data['itemsperinspection']           = $data['inspections'] == 0 ? 0 : round($data['inspectionitems'] / $data['inspections'], 1);
        $data['queens']                       = Queen::count();
        $data['checklists']                   = Checklist::count();
        $data['checklists_edited']            = Checklist::whereRaw('updated_at - created_at > 60')->count();
        $data['sensors']                      = Device::count();
        $data['sensors-online']               = Device::all()->where('online', true)->count();
        $data['researches']                   = [];

        $data['store-measurements-201']       = $this->cacheRequestGetRate('store-measurements-201');
        $data['store-measurements-400']       = $this->cacheRequestGetRate('store-measurements-400', 3600);
        $data['store-measurements-400-array'] = $this->cacheRequestGetArray('store-measurements-400-array');
        $data['store-measurements-401']       = $this->cacheRequestGetRate('store-measurements-401', 3600);
        $data['store-measurements-401-array'] = $this->cacheRequestGetArray('store-measurements-401-array');
        $data['store-measurements-500']       = $this->cacheRequestGetRate('store-measurements-500', 3600);
        $data['store-measurements-500-array'] = $this->cacheRequestGetArray('store-measurements-500-array');
        $data['store-sensors']                = $this->cacheRequestGetRate('store-sensors');
        $data['store-lora-sensors-']          = $this->cacheRequestGetRate('store-lora-sensors-');
        $data['store-lora-sensors-kpn']       = $this->cacheRequestGetRate('store-lora-sensors-kpn');
        $data['store-lora-sensors-kpn-things']= $this->cacheRequestGetRate('store-lora-sensors-kpn-things');
        $data['store-lora-sensors-helium']    = $this->cacheRequestGetRate('store-lora-sensors-helium');
        $data['store-lora-sensors-swisscom']  = $this->cacheRequestGetRate('store-lora-sensors-swisscom');
        $data['store-lora-sensors-ttn-v2']    = $this->cacheRequestGetRate('store-lora-sensors-ttn-v2');
        $data['store-lora-sensors-ttn-v3-pb'] = $this->cacheRequestGetRate('store-lora-sensors-ttn-v3-pb');
        $data['store-lora-sensors-ttn-v3']    = $this->cacheRequestGetRate('store-lora-sensors-ttn-v3');
        $data['store-measurements-total']     = $data['store-sensors'] + $data['store-lora-sensors-'] + $data['store-lora-sensors-kpn'] + $data['store-lora-sensors-kpn-things'] + $data['store-lora-sensors-helium'] + $data['store-lora-sensors-ttn-v2'] + $data['store-lora-sensors-ttn-v3-pb'] + $data['store-lora-sensors-ttn-v3'];
        $data['get-measurements']             = $this->cacheRequestGetRate('get-measurements', 3600);
        $data['get-measurements-last']        = $this->cacheRequestGetRate('get-measurements-last', 3600);
        $data['get-measurements-research']    = $this->cacheRequestGetRate('get-measurements-research', 3600);

        $data['influx-write']                 = $this->cacheRequestGetRate('influx-write', 3600);
        $data['influx-get']                   = $this->cacheRequestGetRate('influx-get', 3600);
        $data['influx-data']                  = $this->cacheRequestGetRate('influx-data', 3600);
        $data['influx-last']                  = $this->cacheRequestGetRate('influx-last', 3600);
        $data['influx-device']                = $this->cacheRequestGetRate('influx-device', 3600);
        $data['influx-names']                 = $this->cacheRequestGetRate('influx-names', 3600);
        $data['influx-names-nocache']         = $this->cacheRequestGetRate('influx-names-nocache', 3600);
        $data['influx-alert']                 = $this->cacheRequestGetRate('influx-alert', 3600);
        $data['influx-flashlog']              = $this->cacheRequestGetRate('influx-flashlog', 3600);
        $data['influx-weather']               = $this->cacheRequestGetRate('influx-weather', 3600);
        $data['influx-research']              = $this->cacheRequestGetRate('influx-research', 3600);
        $data['influx-research-api']          = $this->cacheRequestGetRate('influx-research-api', 3600);
        $data['influx-weight']                = $this->cacheRequestGetRate('influx-weight', 3600);
        $data['influx-csv']                   = $this->cacheRequestGetRate('influx-csv', 3600);

        $data['alert-rules']                  = AlertRule::count();
        $data['alerts']                       = Alert::count();
        $data['alert-direct']                 = $this->cacheRequestGetRate('alert-direct', 3600);
        $data['alert-timed']                  = $this->cacheRequestGetRate('alert-timed', 3600);

        foreach (Research::all() as $key => $r)
        {
            $data['researches'][$key] = [];
            $data['researches'][$key]['name'] = $r->name;
            $data['researches'][$key]['yes']  = DB::table('research_user')->where('research_id', $r->id)->where('consent', 1)->count();
            $data['researches'][$key]['no']   = DB::table('research_user')->where('research_id', $r->id)->where('consent', 0)->count();
        }

        $checklist_details = false;
        $connection        = true;
        
        if ($request->has('checklist_details') && $request->input('checklist_details') == '1')
        {
            $checklist_details = true;
            $data['checklist_categories_max'] = DB::table('checklist_category')
                                                ->selectRaw('checklist_id, COUNT(*) as count')
                                                ->groupBy('checklist_id')
                                                ->having('count', '>', 0)
                                                ->orderBy('count', 'desc')
                                                ->limit(5)
                                                ->get()
                                                ->toArray();

            // $data['checklist_categories_min'] = DB::table('checklist_category')
            //                                     ->selectRaw('checklist_id, COUNT(*) as count')
            //                                     ->groupBy('checklist_id')
            //                                     ->having('count', '>', 0)
            //                                     ->orderBy('count', 'asc')
            //                                     ->limit(5)
            //                                     ->get()
            //                                     ->toArray();

            
            //die(print_r($data['checklist_categories_max']));

            // Only take into account the amount of users with > 10 inspections
            $inspectionUserCount  = DB::table('inspection_user')
                                    ->selectRaw('user_id, COUNT(*) as count')
                                    ->groupBy('user_id')
                                    ->having('count', '>', 10)
                                    ->get()
                                    ->toArray();

            $data['inspection_valid_user_count'] = count($inspectionUserCount);

            $validInspections = collect();
            foreach ($inspectionUserCount as $key => $val) 
            {
                $user_id = $val->user_id;
                $inspections = User::find($user_id)->inspections()->get();
                $validInspections = $validInspections->merge($inspections);
            }
            
            //die(print_r($validInspections->where('impression', '>', -1)->toArray()));
            $countImp = $validInspections->where('impression', '>', -1)->count();
            $countAtt = $validInspections->where('attention', '>', -1)->count();
            $countNts = $validInspections->where('notes', '!=', null)->where('notes', '!=', '')->count();
            $countRmd = $validInspections->where('reminder_date', '!=', null)->count();
            $countRmn = $validInspections->where('reminder', '!=', null)->where('reminder', '!=', '')->count();

            $data['ins_vars'] = [];
            $data['ins_vars'][__('export.impression')]    = ['count'=>$countImp, 'glyphicon'=>'star'];
            $data['ins_vars'][__('export.attention')]     = ['count'=>$countAtt, 'glyphicon'=>'remove-sign'];
            $data['ins_vars'][__('export.notes')]         = ['count'=>$countNts, 'glyphicon'=>'align-left'];
            $data['ins_vars'][__('export.reminder_date')] = ['count'=>$countRmd, 'glyphicon'=>'calendar'];
            $data['ins_vars'][__('export.reminder')]      = ['count'=>$countRmn, 'glyphicon'=>'align-left'];

            $inspection_terms = DB::table('inspection_items')
                                    ->selectRaw('category_id, COUNT(*) as count')
                                    ->groupBy('category_id')
                                    ->having('count', '>', 10)
                                    ->orderBy('count', 'desc')
                                    ->limit(100)
                                    ->get()
                                    ->toArray();

            $transscription                    = [];
            $transscription['smileys_3']       = __('taxonomy.smileys_3');
            $transscription['boolean']         = __('taxonomy.boolean');
            $transscription['boolean_yes_red'] = __('taxonomy.boolean_yes_red');
            $transscription['score_quality']   = __('taxonomy.score_quality');
            $transscription['score_amount']    = __('taxonomy.score_amount');

            $data['terms'] = [];
            foreach ($inspection_terms as $value) 
            {
                $cat  = Category::find($value->category_id);
                $type = $cat->inputTypeType();
                $term = $cat->ancName().$cat->transName();
                $data['terms'][$term] = ['count'=>$value->count, 'glyphicon'=>$cat->inputTypeIcon(), 'type'=>$type];
            }


            //die(print_r($inspection_terms));
            $sensor_counts = [];
            $sensor_total = 0;
            $sensor_count = [];

            // try
            // {
            //     $client = new \Influx;
            //     $sensor_counts = $client::query('SELECT COUNT(*) as "count" FROM "sensors" GROUP BY "name" LIMIT 100')->getPoints();
            // }
            // catch(\Exception $e)
            // {
            //     $connection = $e;
            // }
            // $measurements = Measurement::all()->pluck('pq', 'abbreviation')->toArray();

            // //die(print_r($measurements));

            // if (count($sensor_counts) > 0 && count(reset($sensor_counts)) > 1)
            // {
            //     $arr = reset($sensor_counts);
            //     foreach ($arr as $key => $val) 
            //     {
            //         $sensor_abbr = substr($key, 6);
            //         $sensor_name = in_array($sensor_abbr, array_keys($measurements)) ? $measurements[$sensor_abbr].' ('.$sensor_abbr.')' : null;
            //         //die(print_r($val));

            //         if ($sensor_name != null)
            //         {
            //             $count = intval($val);
            //             $sensor_count[$sensor_name] = $count;
            //             $sensor_total += $count;
            //         }
            //     }

            //     ksort($sensor_count);
            // }
            $data['measurements']= $sensor_total;
            $data['measurement_details']= $sensor_count;
        }
            
        return view('dashboard.index', compact('data', 'connection', 'checklist_details'));
    }
}
