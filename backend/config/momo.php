<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

return [
    'momo_code' => env('MOMO_PARTNER_CODE'),
    'momo_access_key' => env('MOMO_ACCESS_KEY'),
    'momo_secret_key' => env('MOMO_SECRET_KEY'),
    'momo_endpoint' => env('MOMO_ENDPOINT'),
    'momo_notify_url' => env('MOMO_NOTIFY_URL'),
    'momo_return_url' => env('MOMO_RETURN_URL'),
];
