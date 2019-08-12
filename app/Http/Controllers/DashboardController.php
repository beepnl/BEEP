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
use App\Sensor;
use App\Measurement;
use DB;

use Moment\Moment;

class DashboardController extends Controller
{
    public function __construct()
    {
        
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

        $data               = [];
        $data['users']      = User::count();
        $data['newusers']   = User::where('created_at', '>', $last_week)->count();
        $data['hourusers']  = User::where('last_login', '>', $last_hour)->count();
        $data['dayusers']   = User::where('last_login', '>', $last_day)->count();
        $data['activeusers']= User::whereDate('last_login', '>', $last_month)->count();
        $data['qrtusers']   = User::whereDate('last_login', '>', $last_qrt)->count();
        $data['yearusers']  = User::whereDate('last_login', '>', $last_year)->count();
        $data['locations']  = Location::count();
        $data['hives']      = Hive::count();
        $data['frames']     = HiveLayerFrame::count();
        $data['inspections']= Inspection::count();
        $data['inspectionitems']= InspectionItem::count();
        $data['itemsperinspection']= $data['inspections'] == 0 ? 0 : round($data['inspectionitems'] / $data['inspections'], 1);
        $data['queens']     = Queen::count();
        $data['checklists'] = Checklist::count();
        $data['checklists_edited'] = Checklist::whereRaw('updated_at - created_at > 60')->count();
        $data['sensors']    = Sensor::count();

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

            $validInspections = [];
            foreach ($inspectionUserCount as $key => $val) 
            {
                $user_id = $val->user_id;
                $inspections = User::find($user_id)->inspections()->pluck('id')->toArray();
                $validInspections = array_merge($validInspections, $inspections);
            }
            $userInspectionsIds = array_unique($validInspections);
            $validInspections   = Inspection::whereIn('id', $userInspectionsIds)->get();

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
                                    ->whereIn('inspection_id', $userInspectionsIds)
                                    ->groupBy('category_id')
                                    ->having('count', '>', 10)
                                    ->orderBy('count', 'desc')
                                    ->limit(100)
                                    ->get()
                                    ->toArray();

            $transscription                 = [];
            $transscription['smileys_3']    = __('taxonomy.smileys');
            $transscription['boolean']      = __('taxonomy.boolean');
            $transscription['boolean_yes_red'] = __('taxonomy.boolean');
            $transscription['score_quality']= __('taxonomy.quality');
            $transscription['score_amount'] = __('taxonomy.amounts');

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

            try
            {
                $client = new \Influx;
                $sensor_counts = $client::query('SELECT COUNT(*) as "count" FROM "sensors"')->getPoints(); // get first sensor date
            }
            catch(\Exception $e)
            {
                $connection = $e;
            }
            $sensor_count = [];
            $sensor_total = 0;
            $measurements = Measurement::all()->pluck('pq', 'abbreviation')->toArray();

            //die(print_r($measurements));

            if (count($sensor_counts) > 0 && count(reset($sensor_counts)) > 1)
            {
                $arr = reset($sensor_counts);
                foreach ($arr as $key => $val) 
                {
                    $sensor_abbr = substr($key, 6);
                    $sensor_name = in_array($sensor_abbr, array_keys($measurements)) ? $measurements[$sensor_abbr].' ('.$sensor_abbr.')' : null;
                    //die(print_r($val));

                    if ($sensor_name != null)
                    {
                        $count = intval($val);
                        $sensor_count[$sensor_name] = $count;
                        $sensor_total += $count;
                    }
                }

                ksort($sensor_count);
            }
            $data['measurements']= $sensor_total;
            $data['measurement_details']= $sensor_count;
        }
            
        return view('dashboard.index', compact('data', 'connection', 'checklist_details'));
    }
}
