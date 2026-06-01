<?php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

return [

    'webapp_url' => env('WEBAPP_URL', null),

    'providers' => ServiceProvider::defaultProviders()->merge([
        /*
         * Package Service Providers...
         */
        Laravel\Tinker\TinkerServiceProvider::class,
        EllipseSynergie\ApiResponse\Laravel\ResponseServiceProvider::class,
        Collective\Html\HtmlServiceProvider::class,
        Intervention\Image\ImageServiceProvider::class,
        Mcamara\LaravelLocalization\LaravelLocalizationServiceProvider::class,
        TrayLabs\InfluxDB\Providers\ServiceProvider::class,
        //

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
    ])->toArray(),

    'aliases' => Facade::defaultAliases()->merge([
        'Form' => Collective\Html\FormFacade::class,
        'Html' => Collective\Html\HtmlFacade::class,
        'Influx' => TrayLabs\InfluxDB\Facades\InfluxDB::class,
        'InterventionImage' => Intervention\Image\Facades\Image::class,
        'LaravelLocalization' => Mcamara\LaravelLocalization\Facades\LaravelLocalization::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
    ])->toArray(),

];
