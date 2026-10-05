<?php

namespace App\Http\Controllers;

use App\CategoryInput;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryInputsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $keyword = $request->input('search');
        $perPage = 1000;

        if (! empty($keyword)) {
            $categoryinputs = CategoryInput::paginate($perPage);
        } else {
            $categoryinputs = CategoryInput::paginate($perPage);
        }

        return view('categoryinputs.index', compact('categoryinputs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categoryinput = new CategoryInput;

        return view('categoryinputs.create', compact('categoryinput'));
    }

    /**
     * Store a newly created resource in storage.
     *
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string',
            'type' => 'required|string|unique:category_inputs',
            'min' => 'nullable|integer',
            'max' => 'nullable|integer',
            'decimals' => 'nullable|integer',
        ]);

        $requestData = $request->all();

        CategoryInput::create($requestData);

        return redirect('categoryinputs')->with('flash_message', 'CategoryInput added!');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View
    {
        $categoryinput = CategoryInput::findOrFail($id);

        return view('categoryinputs.show', compact('categoryinput'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $categoryinput = CategoryInput::findOrFail($id);

        return view('categoryinputs.edit', compact('categoryinput'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string',
            'type' => Rule::unique('category_inputs')->ignore($id),
            'min' => 'nullable|integer',
            'max' => 'nullable|integer',
            'decimals' => 'nullable|integer',
        ]);

        $categoryinput = CategoryInput::findOrFail($id);
        $requestData = $request->all();

        $categoryinput->update($requestData);

        return redirect('categoryinputs')->with('flash_message', 'CategoryInput updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(int $id): RedirectResponse
    {
        CategoryInput::destroy($id);

        return redirect('categoryinputs')->with('flash_message', 'CategoryInput deleted!');
    }
}
