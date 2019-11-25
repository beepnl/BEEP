<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Research;
use Illuminate\Http\Request;

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
        return view('research.create');
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
            'checklist_id'  => 'nullable|exists:checklists,id',
        ]);

        $requestData = $request->all();
        if (isset($requestData['image']))
            $requestData['image']= Research::storeImage($requestData);

        Research::create($requestData);

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

        return view('research.show', compact('research'));
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
            'checklist_id'  => 'nullable|exists:checklists,id',
        ]);

        $requestData = $request->all();
        
        if (isset($requestData['image']))
            $requestData['image'] = Research::storeImage($requestData);

        $research = Research::findOrFail($id);
        $research->update($requestData);

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
