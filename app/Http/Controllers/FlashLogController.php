<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\FlashLog;
use Illuminate\Http\Request;

class FlashLogController extends Controller
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
            $flashlog = FlashLog::where('user_id', 'LIKE', "%$keyword%")
                ->orWhere('device_id', 'LIKE', "%$keyword%")
                ->orWhere('hive_id', 'LIKE', "%$keyword%")
                ->orWhere('log_messages', 'LIKE', "%$keyword%")
                ->orWhere('log_saved', 'LIKE', "%$keyword%")
                ->orWhere('log_parsed', 'LIKE', "%$keyword%")
                ->orWhere('log_has_timestamps', 'LIKE', "%$keyword%")
                ->orWhere('bytes_received', 'LIKE', "%$keyword%")
                ->orWhere('log_file', 'LIKE', "%$keyword%")
                ->orWhere('log_file_stripped', 'LIKE', "%$keyword%")
                ->orWhere('log_file_parsed', 'LIKE', "%$keyword%")
                ->paginate($perPage);
        } else {
            $flashlog = FlashLog::paginate($perPage);
        }

        return view('flash-log.index', compact('flashlog'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $flashlog = new FlashLog();
        return view('flash-log.create');
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
        
        $requestData = $request->all();
        
        FlashLog::create($requestData);

        return redirect('flash-log')->with('flash_message', 'FlashLog added!');
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
        $flashlog = FlashLog::findOrFail($id);

        return view('flash-log.show', compact('flashlog'));
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
        $flashlog = FlashLog::findOrFail($id);

        return view('flash-log.edit', compact('flashlog'));
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
        
        $requestData = $request->all();
        
        $flashlog = FlashLog::findOrFail($id);
        $flashlog->update($requestData);

        return redirect('flash-log')->with('flash_message', 'FlashLog updated!');
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
        FlashLog::destroy($id);

        return redirect('flash-log')->with('flash_message', 'FlashLog deleted!');
    }
}
