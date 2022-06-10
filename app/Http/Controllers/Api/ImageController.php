<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Auth;
use App\Image;
use Illuminate\Http\Request;

/**
 * @group Api\ImageController
 * Store and retreive image metadata (image_url, thumb_url, width, category_id, etc.)
 * @authenticated
 */
class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $images = Auth::user()->images()->get();

        if ($images)
            return response()->json($images, 200);

        return response()->json(null, 404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        if($request->has('file') && $request->hasFile('file'))
        {
            $image = Image::store($request->all());
            return response()->json($image, 201);
        }
        return response()->json(null, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $image = Auth::user()->images()->findOrFail($id);

        return $image;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        
        $image = Auth::user()->images()->findOrFail($id);
        $image->update($request->all());

        return response()->json($image, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroyByUrl(Request $request)
    {
        if ($request->filled('image_url'))
        {
            $image_url = $request->input('image_url');
            $image     = Auth::user()->images()->where('image_url', $image_url)->orWhere('thumb_url', $image_url)->first();
            if ($image)
            {
                $image->delete();
                return response()->json(null, 204);
            }
        }
        return response()->json(null, 404);
    }
}
