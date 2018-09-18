<?php

return [
    /**
     * Protocol could take values 'http', 'https', 'udp'
     */
    'protocol' => env('LARAVEL_INFLUX_PROVIDER_PROTOCOL', 'http'),
    'user' => env('LARAVEL_INFLUX_PROVIDER_USER', ''),
    'password' => env('LARAVEL_INFLUX_PROVIDER_PASSWORD', ''),
    'host' => env('LARAVEL_INFLUX_PROVIDER_HOST', 'localhost'),
    'port' => env('LARAVEL_INFLUX_PROVIDER_PORT', '8086'),
    'database' => env('LARAVEL_INFLUX_PROVIDER_DATABASE', 'main'),

    /**
     * Use Queue for sending to InfluxDB, if 'true'
     */
    'use_queue' => env('LARAVEL_INFLUX_PROVIDER_USE_QUEUE', 'false'),

    /**
     * Queue name
     */
    'queue_name' => env('LARAVEL_INFLUX_PROVIDER_QUEUE_NAME', 'influx'),

    /**
     * Use InfluxDB for error/exception collector if 'true'
     */
    'use_monolog_handler' => env('LARAVEL_INFLUX_PROVIDER_USE_MONOLOG_HANDLER', 'false'),

    /**
     * Logging level should take one of the given values:
     *     'DEBUG', 'INFO', 'NOTICE', 'WARNING', 'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'
     */
    'logging_level' => env('LARAVEL_INFLUX_PROVIDER_LOGGING_LEVEL', 'NOTICE'),

    /**
     * Log field fields.Debug.message will be trimmed to given value of lines.
     * Use 0 for no limit
     */
    'log_message_lines_limit' => env('LARAVEL_INFLUX_PROVIDER_LOG_MESSAGE_LINES_LIMIT', 5),
];
