<?php

return [
    'api_key'   => env('BULKSMSBD_API_KEY'),
    'sender_id' => env('BULKSMSBD_SENDER_ID'),
    'base_url'  => env('BULKSMSBD_BASE_URL', 'https://bulksmsbd.net/api'),
];
