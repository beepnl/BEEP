<?php

return [

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'icons' => [
            'driver' => 'local',
            'root' => storage_path('app/public/icons'),
            'url' => env('APP_URL').'/storage/icons',
            'visibility' => 'public',
        ],

        'exports' => [
            'driver' => 'local',
            'root' => storage_path('exports'),
            'url' => env('APP_URL').'/storage/exports',
            'visibility' => 'private',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'cache' => [
                'store' => 'redis',
                'expire' => 600,
                'prefix' => 'cache-',
            ],
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],
    ],

];
