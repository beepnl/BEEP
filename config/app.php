<?php

use Illuminate\Support\Facades\Facade;

return [

    'webapp_url' => env('WEBAPP_URL', null),

    'aliases' => Facade::defaultAliases()->merge([
        'Influx' => TrayLabs\InfluxDB\Facades\InfluxDB::class,
        'InterventionImage' => Intervention\Image\Facades\Image::class,
        'LaravelLocalization' => Mcamara\LaravelLocalization\Facades\LaravelLocalization::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
    ])->toArray(),

];
