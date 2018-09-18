<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Location;
use App\Hive;
use App\Inspection;
use App\Queen;
use App\Checklist;
use App\HiveLayerFrame;

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
        $data['queens']		= Queen::count();
        $data['checklists']	= Checklist::count();
            
        return view('dashboard.index', compact('data'));
    }
}
