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
        $moment 	 		= new Moment();
        $last_hour          = $moment->subtractHours(1)->format('Y-m-d H:i:s');
        $last_day           = $moment->subtractDays(1)->format('Y-m-d H:i:s');
        $last_week          = $moment->subtractDays(7)->format('Y-m-d H:i:s');
        $last_month 		= $moment->subtractMonths(1)->format('Y-m-d');
        $last_qrt  			= $moment->subtractMonths(3)->format('Y-m-d');
        $last_year  		= $moment->subtractMonths(12)->format('Y-m-d');
        //die(print_r($last_day));

        $data 				= [];
        $data['users'] 		= User::count();
        $data['newusers']   = User::where('created_at', '>', $last_week)->count();
        $data['hourusers']  = User::where('last_login', '>', $last_hour)->count();
        $data['dayusers']   = User::where('last_login', '>', $last_day)->count();
        $data['activeusers']= User::whereDate('last_login', '>', $last_month)->count();
        $data['qrtusers']   = User::whereDate('last_login', '>', $last_qrt)->count();
        $data['yearusers']  = User::whereDate('last_login', '>', $last_year)->count();
        $data['locations']	= Location::count();
        $data['hives']		= Hive::count();
        $data['frames']		= HiveLayerFrame::count();
        $data['inspections']= Inspection::count();
        $data['inspectionitems']= InspectionItem::count();
        $data['itemsperinspection']= round($data['inspectionitems'] / $data['inspections'], 1);
        $data['queens']		= Queen::count();
        $data['checklists'] = Checklist::count();
        $data['checklists_edited'] = Checklist::whereRaw('updated_at - created_at > 60')->count();

        $data['checklist_categories_max'] = DB::table('checklist_category')
                                            ->selectRaw('checklist_id, COUNT(*) as count')
                                            ->groupBy('checklist_id')
                                            ->having('count', '>', 0)
                                            ->orderBy('count', 'desc')
                                            ->limit(5)
                                            ->get()
                                            ->toArray();

        $data['checklist_categories_min'] = DB::table('checklist_category')
                                            ->selectRaw('checklist_id, COUNT(*) as count')
                                            ->groupBy('checklist_id')
                                            ->having('count', '>', 0)
                                            ->orderBy('count', 'asc')
                                            ->limit(5)
                                            ->get()
                                            ->toArray();

        //die(print_r($data['checklist_categories_max']));

        // add top 20 used inspection items
        $data['ins_vars'][__('export.impression')] = ['count'=>Inspection::where('impression', '>', -1)->count(), 'glyphicon'=>'star'];
        $data['ins_vars'][__('export.attention')]  = ['count'=>Inspection::where('attention', '>', -1)->count(), 'glyphicon'=>'remove-sign'];
        $data['ins_vars'][__('export.notes')] = ['count'=>Inspection::where('notes', '!=', null)->where('notes', '!=', '')->count(), 'glyphicon'=>'align-left'];
        $data['ins_vars'][__('export.reminder_date')] = ['count'=>Inspection::where('reminder_date', '!=', null)->count(), 'glyphicon'=>'calendar'];
        $data['ins_vars'][__('export.reminder')] = ['count'=>Inspection::where('reminder', '!=', null)->where('reminder', '!=', '')->count(), 'glyphicon'=>'align-left'];

        $data['terms'] = [];

        $inspection_terms = DB::table('inspection_items')
                                ->selectRaw('category_id, COUNT(*) as count')
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

        foreach ($inspection_terms as $value) 
        {
            $cat  = Category::find($value->category_id);
            $type = $cat->inputTypeType();
            $term = $cat->ancName().$cat->transName();
            $data['terms'][$term] = ['count'=>$value->count, 'glyphicon'=>$cat->inputTypeIcon(), 'type'=>$type];
        }

        //die(print_r($inspection_terms));
            
        return view('dashboard.index', compact('data'));
    }
}
