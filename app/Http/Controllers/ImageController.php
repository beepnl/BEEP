<?php

namespace App\Http\Controllers;

use App\Image;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $keyword = $request->get('search');
        $perPage = 25;

        if (! empty($keyword)) {
            $image = Image::where('file', 'LIKE', "%$keyword%")
                ->orWhere('description', 'LIKE', "%$keyword%")
                ->orWhere('type', 'LIKE', "%$keyword%")
                ->orWhere('height', 'LIKE', "%$keyword%")
                ->orWhere('width', 'LIKE', "%$keyword%")
                ->orWhere('size_kb', 'LIKE', "%$keyword%")
                ->orWhere('date', 'LIKE', "%$keyword%")
                ->paginate($perPage);
        } else {
            $image = Image::paginate($perPage);
        }

        return view('image.index', compact('image'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('image.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request): RedirectResponse
    {

        $requestData = $request->all();

        Image::store($requestData);

        return redirect('image')->with('flash_message', 'Image added!');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View
    {
        $image = Image::findOrFail($id);

        return view('image.show', compact('image'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $image = Image::findOrFail($id);

        return view('image.edit', compact('image'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, int $id): RedirectResponse
    {

        $requestData = $request->all();

        $image = Image::findOrFail($id);
        $image->update($requestData);

        return redirect('image')->with('flash_message', 'Image updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(int $id): RedirectResponse
    {
        Image::findOrFail($id)->delete();

        return redirect('image')->with('flash_message', 'Image deleted!');
    }
}
