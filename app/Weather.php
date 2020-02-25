<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Location;
use App\Controllers\Api\MeasurementController;
use Storage;
use DarkSky;
use InfluxDB;
use LaravelLocalization;
use GuzzleHttp\Exception\GuzzleException;

class Weather extends Model
{
    /*
    SI units are as follows:

    summary: Any summaries containing temperature or snow accumulation units will have their values in degrees Celsius or in centimeters (respectively).
    nearestStormDistance: Kilometers.
    precipIntensity: Millimeters per hour.
    precipIntensityMax: Millimeters per hour.
    precipAccumulation: Centimeters.
    temperature: Degrees Celsius.
    temperatureMin: Degrees Celsius.
    temperatureMax: Degrees Celsius.
    apparentTemperature: Degrees Celsius.
    dewPoint: Degrees Celsius.
    windSpeed: Meters per second.
    windGust: Meters per second.
    pressure: Hectopascals.
    visibility: Kilometers.
    
    */

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

        $disk     = 'public';
        $includes = ['currently','alerts']; // 'daily','hourly'
        $filename = 'weather/lat_'.$lat.'_lon_'.$lon.'_'.implode('_', $includes).'.json';

        if (Storage::disk($disk)->exists($filename))
        {
            $seconds = env('DARKSKY_API_CHECK_MINUTES', 10)*60;
            $now_sec = time();
            $lst_sec = Storage::disk($disk)->exists($filename) ? Storage::disk($disk)->lastModified($filename) : 0;
            $dif_sec = $now_sec - $lst_sec;

            if ($dif_sec < $seconds) // check if cached file should be displayed
            {
                if ($location)
                {
                    $location->last_weather_time = date('Y-m-d H:i:s', $lst_sec);
                    $location->save();
                }
                return Storage::disk($disk)->get($filename);
            }
        }
        
        // file not found yet
        $lang  = LaravelLocalization::getCurrentLocale();
        $units = env('DARKSKY_API_UNITS', 'si');

        try {
           $result = DarkSky::location($lat, $lon)->language($lang)->units($units)->includes($includes)->get();
        } catch (GuzzleException $e) {
            return ['error'=>['msg'=>$e->getResponse()->getBody()->getContents(), 'code'=>$e->getResponse()->getStatusCode()]];
        }
       
        if ($result)
        {
            $out = json_encode($result);
            Storage::disk($disk)->put($filename, $out);

            // save data to Influx by lat/lon
            if (isset($result->currently))
            {
                $time = $result->currently->time;
                unset($result->currently->time);
                unset($result->currently->summary);
                Weather::storeDataToInflux($result->currently, $lat, $lon, $time);
            }

            if ($location)
            {
                $location->last_weather_time = date('Y-m-d H:i:s');
                $location->save();
            }
            return $out;
        }

        return ['error'=>['msg'=>'no_data','code'=>500]];
    }

    public static function updateLocations()
    {
        $locations = Location::where('coordinate_lat', '!=', null)->where('coordinate_lon', '!=', null)->get();

        $count = 0;

        foreach ($locations as $loc) 
        {
            if ($loc->devices()->count() > 0)
            {
                $result = Weather::callApi($loc);
                if (gettype($result) == 'string')
                    $count++;
            }
        }
        return 'updated_'.$count.'_locations';
    }

    private static function storeDataToInflux($data_array, $lat, $lon, $unix_timestamp=null) // store posted data
    {
        $client    = new \Influx;
        $points    = [];
        $unix_time = isset($unix_timestamp) ? $unix_timestamp : time();
        $valid_sens= Measurement::all()->pluck('pq', 'abbreviation')->toArray();

        foreach ($data_array as $key => $value) 
        {
            if (in_array($key, array_keys($valid_sens)) )
            {
                switch($key)
                {
                    case 'summary':
                    case 'icon':
                    case 'precipType':
                        $val = $value;
                        break;
                    default:
                        $val = floatval($value);
                }

                array_push($points, 
                    new InfluxDB\Point(
                        'weather',                  // name of the measurement
                        null,                       // the measurement value
                        ['lat'=>$lat, 'lon'=>$lon],        // optional tags
                        ["$key" => $val], // key value pairs
                        $unix_time                  // Time precision has to be set to InfluxDB\Database::PRECISION_SECONDS!
                    )
                );
            }
        }
        //die(print_r($points));
        $stored = false;
        if (count($points) > 0)
        {
            try
            {
                $stored = $client::writePoints($points, InfluxDB\Database::PRECISION_SECONDS);
            }
            catch(\Exception $e)
            {
                // gracefully do nothing
            }
        }
        return $stored;
    }
}
