<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\CategoryInput;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryInputsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $keyword = $request->get('search');
        $perPage = 1000;

        if (!empty($keyword)) {
            $categoryinputs = CategoryInput::paginate($perPage);
        } else {
            $categoryinputs = CategoryInput::paginate($perPage);
        }

        return view('categoryinputs.index', compact('categoryinputs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $categoryinput = new CategoryInput();
        return view('categoryinputs.create', compact('categoryinput'));
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
        $this->validate($request,
        [
            'name'                  => 'required|string',
            'type'                  => 'required|string|unique:category_inputs',
            'min'                   => 'nullable|integer',
            'max'                   => 'nullable|integer',
            'decimals'              => 'nullable|integer',
        ]);

        $requestData = $request->all();
        
        CategoryInput::create($requestData);

        return redirect('categoryinputs')->with('flash_message', 'CategoryInput added!');
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
        $categoryinput = CategoryInput::findOrFail($id);

        return view('categoryinputs.show', compact('categoryinput'));
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
        $categoryinput = CategoryInput::findOrFail($id);

        return view('categoryinputs.edit', compact('categoryinput'));
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
        $this->validate($request,
        [
            'name'                  => 'required|string',
            'type'                  => Rule::unique('category_inputs')->ignore($id),
            'min'                   => 'nullable|integer',
            'max'                   => 'nullable|integer',
            'decimals'              => 'nullable|integer',
        ]);
        
        $categoryinput = CategoryInput::findOrFail($id);
        $requestData = $request->all();
        
        $categoryinput->update($requestData);

        return redirect('categoryinputs')->with('flash_message', 'CategoryInput updated!');
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
        CategoryInput::destroy($id);

        return redirect('categoryinputs')->with('flash_message', 'CategoryInput deleted!');
    }
}
