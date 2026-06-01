<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\HiveTag;
use Illuminate\Http\Request;

class HiveTagsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $keyword = $request->get('search');
        $perPage = 25;

        if (! empty($keyword)) {
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
     */
    public function create(): View
    {
        $hivetag = new HiveTag;

        return view('hive-tags.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'tag' => 'required',
            'user_id' => 'required',
        ]);
        $requestData = $request->all();

        HiveTag::create($requestData);

        return redirect('hive-tags')->with('flash_message', 'HiveTag added!');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View
    {
        $hivetag = HiveTag::findOrFail($id);

        return view('hive-tags.show', compact('hivetag'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $hivetag = HiveTag::findOrFail($id);

        return view('hive-tags.edit', compact('hivetag'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $this->validate($request, [
            'tag' => 'required',
            'user_id' => 'required',
        ]);
        $requestData = $request->all();

        $hivetag = HiveTag::findOrFail($id);
        $hivetag->update($requestData);

        return redirect('hive-tags')->with('flash_message', 'HiveTag updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(int $id): RedirectResponse
    {
        HiveTag::destroy($id);

        return redirect('hive-tags')->with('flash_message', 'HiveTag deleted!');
    }
}
