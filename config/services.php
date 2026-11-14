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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // Token publik (pk.*) yang sama dengan yang sudah dipakai langsung di
    // resources/views/front/tracking.blade.php -- aman dipakai server-side juga,
    // default-nya token yang sama supaya tidak perlu ubah .env kalau tidak mau.
    'mapbox' => [
        'token' => env('MAPBOX_TOKEN', 'pk.eyJ1IjoiaXJmYW5yb3NqaWRpIiwiYSI6ImNrNjc0bjU2bTE2YjYzcnA5YnpwOTM5dnEifQ.ZzyiAbCzUk_MbkmUi-ICRQ'),
    ],

];
