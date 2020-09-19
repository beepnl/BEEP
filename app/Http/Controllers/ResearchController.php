<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Research;

use DB;
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
    public function show($id)
    {
        $research = Research::findOrFail($id);

        $consents = DB::table('research_user')->where('research_id', $id)->whereDate('updated_at', '>=', $research->start_date)->whereDate('updated_at', '<', $research->end_date)->orderBy('user_id')->orderBy('updated_at','desc')->get();

        // Make dates table
        $dates = [];

        $moment_start = new Moment($research->start_date);
        $moment_end   = new Moment($research->end_date);
        $moment_now   = new Moment();

        if ($moment_now < $moment_end)
            $moment_end = $moment_now;
            
        $moment_start = $moment_start->endof('day');
        $moment = $moment_end->startof('day');
        $assets = ["users"=>0, "apiaries"=>0, "hives"=>0, "inspections"=>0, "devices"=>0, "measurements"=>0];
        
        while($moment > $moment_start)
        {
            $dates[$moment->format('Y-m-d')] = $assets;
            $moment = $moment->addDays(-1);
        }

        // count user consents within dates



        // try
        // {
        //     $client = new \Influx;
        //     $sensor_counts = $client::query('SELECT COUNT(*) as "count" FROM "sensors"')->getPoints(); // get first sensor date
        // }
        // catch(\Exception $e)
        // {
        //     $connection = $e;
        // }
        // $sensor_count = [];
        // $sensor_total = 0;
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
        // $data['measurements']= $sensor_total;
        // $data['measurement_details']= $sensor_count;

        return view('research.show', compact('research', 'consents', 'dates'));
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
