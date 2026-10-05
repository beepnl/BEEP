<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Cache Store
    |--------------------------------------------------------------------------
    |
    | This option controls the default cache store that will be used by the
    | framework. This connection is utilized if another isn't explicitly
    | specified when running a cache operation inside the application.
    |
    */

    // 'default' => env('CACHE_STORE', 'redis'),
    'default' => env('CACHE_DRIVER', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the cache "stores" for your application as
    | well as their drivers. You may even define multiple stores for the
    | same cache driver to group types of items stored in your caches.
    |
    | Supported drivers: "array", "database", "file", "memcached",
    |                    "redis", "dynamodb", "octane", "null"
    |
    */

    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache'),
            'lock_path' => storage_path('framework/cache/data'),
        ],
        // 'redis' => [
        //     'driver' => 'redis',
        //     'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
        //     'lock_connection' => env('REDIS_CACHE_LOCK_CONNECTION', 'default'),
        // ],
    ],

    // /*
    // |--------------------------------------------------------------------------
    // | Cache Key Prefix
    // |--------------------------------------------------------------------------
    // |
    // | When utilizing the APC, database, memcached, Redis, and DynamoDB cache
    // | stores, there might be other applications using the same cache. For
    // | that reason, you may prefix every cache key to avoid collisions.
    // |
    // */

    // 'prefix' => env('CACHE_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_cache_'),
];
