<?php

use Illuminate\Support\Facades\Facade;

return [

    'webapp_url' => env('WEBAPP_URL', null),

    'aliases' => Facade::defaultAliases()->merge([
        'Form' => Collective\Html\FormFacade::class,
        'Html' => Collective\Html\HtmlFacade::class,
        'Influx' => TrayLabs\InfluxDB\Facades\InfluxDB::class,
        'InterventionImage' => Intervention\Image\Facades\Image::class,
        'LaravelLocalization' => Mcamara\LaravelLocalization\Facades\LaravelLocalization::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
    ])->toArray(),

];
