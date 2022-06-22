<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Models\HiveTag;
use Illuminate\Http\Request;

class HiveTagsController extends Controller
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
            $hivetags = HiveTag::where('user_id', 'LIKE', "%$keyword%")
                ->orWhere('tag', 'LIKE', "%$keyword%")
                ->orWhere('hive_id', 'LIKE', "%$keyword%")
                ->orWhere('url', 'LIKE', "%$keyword%")
                ->orWhere('acrion_id', 'LIKE', "%$keyword%")
                ->orWhere('router', 'LIKE', "%$keyword%")
                ->paginate($perPage);
        } else {
            $hivetags = HiveTag::paginate($perPage);
        }

        return view('hive-tags.index', compact('hivetags'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $hivetag = new HiveTag();
        return view('hive-tags.create');
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
			'tag' => 'required',
			'user_id' => 'required'
		]);
        $requestData = $request->all();
        
        HiveTag::create($requestData);

        return redirect('hive-tags')->with('flash_message', 'HiveTag added!');
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
        $hivetag = HiveTag::findOrFail($id);

        return view('hive-tags.show', compact('hivetag'));
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
        $hivetag = HiveTag::findOrFail($id);

        return view('hive-tags.edit', compact('hivetag'));
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
			'tag' => 'required',
			'user_id' => 'required'
		]);
        $requestData = $request->all();
        
        $hivetag = HiveTag::findOrFail($id);
        $hivetag->update($requestData);

        return redirect('hive-tags')->with('flash_message', 'HiveTag updated!');
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
        HiveTag::destroy($id);

        return redirect('hive-tags')->with('flash_message', 'HiveTag deleted!');
    }
}
