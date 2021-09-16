<?php

namespace App\Http\Controllers\Api;

use App\Models\FlashLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Moment\Moment;

/**
 * @group Api\FlashLogController
 */
class FlashLogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $flashlogs = $request->user()->flashlogs()->orderByDesc('created_at')->get();
        return response()->json($flashlogs);
    }


    public function try(Request $request, $id)
    {
        return response()->json('try_not_yet_implemented');
    }

    public function commit(Request $request, $id)
    {
        return response()->json('commit_not_yet_implemented');
    }

    public function destroy(Request $request, $id)
    {
        return response()->json('destroy_not_yet_implemented');
        //return response()->json($request->user()->flashlogs()->findOrFail($id)->delete());
    }

}
