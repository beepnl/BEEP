<?php

/*
|--------------------------------------------------------------------------
| Allowed Origins
|--------------------------------------------------------------------------
|
| ALLOWED_ORIGINS in .env is a comma separated list of EXACT origins, e.g.
|
|   ALLOWED_ORIGINS="https://app.beep.nl,https://my-dashboard.example.com"
|
| The underlying stack (asm89/stack-cors) matches these values literally.
| Only the single value * means "allow every origin"; a subdomain wildcard
| such as *.beep.nl does NOT match anything. If you need wildcards, put a
| regular expression in 'allowed_origins_patterns' below instead.
|
*/

$allowed_origins = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('ALLOWED_ORIGINS', 'https://app.beep.nl'))
)));

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS Options
    |--------------------------------------------------------------------------
    |
    | The allowed_methods and allowed_headers options are case-insensitive.
    |
    | You don't need to provide both allowed_origins and allowed_origins_patterns.
    | If one of the strings passed matches, it is considered a valid origin.
    |
    | If ['*'] is provided to allowed_methods, allowed_origins or allowed_headers
    | all methods / origins / headers are allowed.
    |
    */

    /*
     * You can enable CORS for 1 or multiple paths.
     * Example: ['api/*']
     */
    'paths' => ['api/*'],

    /*
    * Matches the request method. `['*']` allows all methods.
    */
    'allowed_methods' => ['*'],

    /*
     * Matches the request origin. `['*']` allows all origins. Wildcards can be used, eg `*.mydomain.com`
     */
    'allowed_origins' => $allowed_origins,

    /*
     * Patterns that can be used with `preg_match` to match the origin.
     */
    'allowed_origins_patterns' => [],

    /*
     * Sets the Access-Control-Allow-Headers response header. `['*']` allows all headers.
     */
    'allowed_headers' => ['*'],

    /*
     * Sets the Access-Control-Expose-Headers response header with these headers.
     */
    'exposed_headers' => [],

    /*
     * Sets the Access-Control-Max-Age response header when > 0.
     */
    'max_age' => 0,

    /*
     * Sets the Access-Control-Allow-Credentials header.
     */
    'supports_credentials' => false,
];
