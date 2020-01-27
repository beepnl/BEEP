<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Storage;
use DarkSky;
use LaravelLocalization;
use GuzzleHttp\Exception\GuzzleException;

class Weather extends Model
{
    public static function callApi($location=null, $lat=null, $lon=null)
    {
    	$result = null;

        if ($lat == null || $lon == null)
        {
            if ($location && isset($location->coordinate_lat) && isset($location->coordinate_lon))
            {
                $lat = $location->coordinate_lat;
                $lon = $location->coordinate_lon;
            }
            else
            {
                return ['error'=>['msg'=>'no_coordinates','code'=>500]];
            }
        }

        if ($location)
        {
            $seconds = env('DARKSKY_API_CHECK_MINUTES', 10)*60;
            $now_sec = time();
            $dif_sec = $now_sec - $location->last_weather_time;

            if ($dif_sec < $seconds) // check if cached file should be displayed
                return ['error'=>['msg'=>'refresh_too_soon','code'=>500]];
        }
            
        $lang  = LaravelLocalization::getCurrentLocale();
        $units = env('DARKSKY_API_UNITS', 'si');

        try {
           $result = DarkSky::location($lat, $lon)->language($lang)->units($units)->includes(['currently','daily','hourly','alerts'])->get();
        } catch (GuzzleException $e) {
            return ['error'=>['msg'=>$e->getResponse()->getBody()->getContents(), 'code'=>$e->getResponse()->getStatusCode()]];
        }
       
        if ($result)
        {
            if ($location)
            {
                $location->last_weather_time = time();
                $location->save();
            }
            return json_encode($result);
        }

        return ['error'=>['msg'=>'no_data','code'=>500]];
    }
}
