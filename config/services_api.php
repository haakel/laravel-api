<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SSL Verification
    |--------------------------------------------------------------------------
    |
    | Set to false for local development when external APIs have SSL issues.
    | Always leave as true in production.
    |
    | .env:  API_SSL_VERIFY=true   (production / normal)
    |        API_SSL_VERIFY=false  (local dev / proxy mode)
    |
    */
    'ssl_verify' => env('API_SSL_VERIFY', true),

];
