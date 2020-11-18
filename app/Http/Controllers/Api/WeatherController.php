<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Weather;
use Illuminate\Http\Request;

/**
 * @group Api\WeatherController
 *
 * Weather data request
 */
class WeatherController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $lat      = $request->filled('lat') ? $request->input('lat') : null;
        $lon      = $request->filled('lon') ? $request->input('lon') : null;
        $location = $request->filled('location_id') ? $request->user()->locations()->where('coordinate_lat', '!=', null)->where('coordinate_lon', '!=', null)->find($request->input('location_id')) : null;
        
        $result   = Weather::callApi($location, $lat, $lon);

        if (gettype($result) == 'string')
            return response($result)->header('Content-Type','application/json'); // header required for $result has to be read from disk also

        if (gettype($result) == 'array' && isset($result['error']))
            return response()->json($result['error']['msg'], $result['error']['code']);

        return response()->json('error_type_is_'.gettype($result), 500);
    }
}

