<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Location;
use App\Controllers\Api\MeasurementController;
use Storage;
use InfluxDB;
use LaravelLocalization;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Cache;

class Weather extends Model
{
    public static function cacheRequestRate($name)
    {
        Cache::remember($name.'-time', 86400, function () use ($name)
        { 
            Cache::forget($name.'-count'); 
            return time(); 
        });

        if (Cache::has($name.'-count'))
            Cache::increment($name.'-count');
        else
            Cache::put($name.'-count', 1);

    }

    /*
    SI units are as follows:

    "main": {
        "temp": 272.33,
        "feels_like": 262.58,
        "temp_min": 271.48,
        "temp_max": 273.15,
        "pressure": 1040,
        "humidity": 10
    },
    
    Old (Darksky)
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
        $filename = 'weather/lat_'.$lat.'_lon_'.$lon.'.json';

        if (Storage::disk($disk)->exists($filename))
        {
            $seconds = env('OPENWEATHER_API_CHECK_MINUTES', 10)*60;
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
        $result   = null;
        $lang     = LaravelLocalization::getCurrentLocale();
        $units    = env('OPENWEATHER_API_UNITS', 'metric');
        $url      = env('OPENWEATHER_API_URL').'&lat='.$lat.'&lon='.$lon.'&units='.$units; 
        
        try
        {
            $guzzle   = new Client();
            $response = $guzzle->request('GET', $url, ['verify' => true, 'http_errors' => false]);
            if ($response->getStatusCode() == 200)
                $result = json_decode($response->getBody());  
        }
        catch(\Exception $e)
        {
            // gracefully do nothing
        }
       
        if ($result)
        {
            $out = json_encode($result);
            Storage::disk($disk)->put($filename, $out);

            // save data to Influx by lat/lon
            if (isset($result->main))
            {
                Weather::storeDataToInflux($result);
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
        $time  = time();
        Log::info('Weather::updateLocations()');

        foreach ($locations as $loc) 
        {
            if ($loc->device_count() > 0)
            {
                $result = Weather::callApi($loc);
                if (gettype($result) == 'string') // array is error
                    $count++;
            }
        }
        $secs = time() - $time;
        $msg  = "Weather::updateLocations() updated $count locations in $secs sec";

        Log::info($msg);
        return $msg;
    }

    /*
    API docs: https://openweathermap.org/current
    Call: https://api.openweathermap.org/data/2.5/weather?appid=ade322e3f12184da662ed2723eb72852&lat=52.0451023&lon=5.2935263&lang=nl&units=metric
    Result:
    {
        "coord": {
            "lon": 5.2935,
            "lat": 52.0451
        },
        "weather": [
            {
                "id": 800, (https://openweathermap.org/weather-conditions)
                "main": "Clear",
                "description": "clear sky",
                "icon": "01d" (https://openweathermap.org/weather-conditions)
            }
        ],
        "base": "stations",
        "main": {
            "temp": -2.06, Unit Default: Kelvin, Metric: Celsius, Imperial: Fahrenheit.
            "feels_like": -5, Unit Default: Kelvin, Metric: Celsius, Imperial: Fahrenheit.
            "temp_min": -3.3, Minimum temperature at the moment. This is minimal currently observed temperature (within large megalopolises and urban areas). Unit Default: Kelvin, Metric: Celsius, Imperial: Fahrenheit.
            "temp_max": -1.67, Maximum temperature at the moment. This is maximal currently observed temperature (within large megalopolises and urban areas). Unit Default: Kelvin, Metric: Celsius, Imperial: Fahrenheit.
            "pressure": 1039, (hPa)
            "humidity": 75 (%)
        },
        "visibility": 10000,
        "wind": {
            "speed": 2.68, (meter/sec)
            "deg": 98, (degrees (meteorological; 0=north, 90=east, 180=south, 270=west))
            "gust": 5.36 (Unit Default: meter/sec)
        },
        "clouds": {
            "all": 0
        },
        "rain":{
            "1h": 20.4 (mm)
            "3h": 50 (mm)
        }
        "snow":{
            "1h": 10.4 (mm)
            "3h": 20.8 (mm)
        }
        "dt": 1613215266,
        "sys": {
            "type": 3,
            "id": 2009768,
            "country": "NL",
            "sunrise": 1613199471,
            "sunset": 1613234905
        },
        "timezone": 3600,
        "id": 2756619,
        "name": "Driebergen-Rijsenburg",
        "cod": 200
    }
    */
    private static function storeDataToInflux($result) // store posted data
    {
        $client     = new \Influx;
        $points     = [];
        $data_array = [];

        $valid_sens = Measurement::all()->pluck('pq', 'abbreviation')->toArray();
        $unix_time  = isset($result->dt) ? intval($result->dt) : time();
        $lat        = isset($result->coord->lat) ? floatval($result->coord->lat) : null;
        $lon        = isset($result->coord->lon) ? floatval($result->coord->lon) : null;

        if (isset($lat) && isset($lon))
        {
            foreach ($result as $key => $value) 
            {
                switch($key)
                {
                    case 'weather':
                        if (isset($value->id)){ $data_array['weather_id'] = $value->id; };
                        if (isset($value->icon)){ $data_array['weather_icon'] = $value->icon; };
                        if (isset($value->main)){ $data_array['weather_name'] = $value->main; };
                        if (isset($value->description)){ $data_array['weather_description'] = $value->description; };
                        break;
                    case 'main':
                        if (isset($value->temp)){ $data_array['temperature'] = $value->temp; };
                        if (isset($value->temp_min)){ $data_array['temperatureMin'] = $value->temp_min; };
                        if (isset($value->temp_max)){ $data_array['temperatureMax'] = $value->temp_max; };
                        if (isset($value->feels_like)){ $data_array['apparentTemperature'] = $value->feels_like; };
                        if (isset($value->pressure)){ $data_array['pressure'] = $value->pressure; };
                        if (isset($value->sea_level)){ $data_array['pressure'] = $value->sea_level; };
                        if (isset($value->grnd_level)){ $data_array['pressure'] = $value->grnd_level; };
                        if (isset($value->humidity)){ $data_array['humidity'] = $value->humidity; };
                        break;
                    case 'visibility':
                        $data_array['visibility'] = $value;
                        break;
                    case 'clouds':
                        if (isset($value->all)){ $data_array['cloudiness'] = $value->all; };
                        break;
                    case 'wind':
                        if (isset($value->speed)){ $data_array['windSpeed'] = $value->speed; };
                        if (isset($value->deg)){ $data_array['windBearing'] = $value->deg; };
                        if (isset($value->gust)){ $data_array['windGust'] = $value->gust; };
                        break;
                    case 'rain':
                    case 'snow':
                        if (isset($value->{'1h'})){ $data_array['precipIntensity'] = $value->{'1h'}; };
                        if (isset($value->{'3h'})){ $data_array[$key.'_3h'] = $value->{'3h'}; };
                        break;
                    default:
                        // no action
                }
            }
            //die(print_r($data_array));
            foreach ($data_array as $key => $value) 
            {
                if (in_array($key, array_keys($valid_sens)) )
                {
                    switch($key)
                    {
                        case 'weather_name':
                        case 'weather_icon':
                        case 'weather_description':
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
        }
        //die(print_r($points));
        $stored = false;
        if (count($points) > 0)
        {
            Weather::cacheRequestRate('influx-write');
            try
            {
                $stored = $client::writePoints($points, InfluxDB\Database::PRECISION_SECONDS);
            }
            catch(\Exception $e)
            {
                // gracefully do nothing
                Log::error($e);
            }
        }
        return $stored;
    }
}
