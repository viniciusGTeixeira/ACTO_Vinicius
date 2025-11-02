<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // ArcGIS Maps SDK Configuration
    'arcgis' => [
        'api_key' => env('ARCGIS_API_KEY'),
        'basemap' => env('ARCGIS_BASEMAP', 'topo-vector'),
        'center_lat' => env('ARCGIS_CENTER_LAT', -15.7801),
        'center_lng' => env('ARCGIS_CENTER_LNG', -47.9292),
        'zoom_level' => env('ARCGIS_ZOOM_LEVEL', 4),
    ],

    // Evolution API (WhatsApp) Configuration
    'evolution_api' => [
        'url' => env('EVOLUTION_API_URL'),
        'key' => env('EVOLUTION_API_KEY'),
        'instance_name' => env('EVOLUTION_INSTANCE_NAME'),
    ],

    // GeoIP Configuration
    'geoip' => [
        'enabled' => env('GEOIP_ENABLED', true),
        'api_url' => env('GEOIP_API_URL', 'https://ipapi.co'),
        'api_key' => env('GEOIP_API_KEY'),
        'cache_duration' => env('GEOIP_CACHE_DURATION', 86400),
    ],

];
