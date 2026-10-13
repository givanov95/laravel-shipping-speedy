<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Speedy
    |--------------------------------------------------------------------------
    |
    | The API of Speedy (https://api.speedy.bg/v1) and the account it is called
    | with. Speedy has no header for the credentials: they travel in the body
    | of every request, with the language of the names it answers in.
    |
    */
    'base_url' => env('SPEEDY_BASE_URL'),
    'username' => env('SPEEDY_USERNAME'),
    'password' => env('SPEEDY_PASSWORD'),
    'language' => env('SPEEDY_LANGUAGE', 'EN'),

    /*
    |--------------------------------------------------------------------------
    | Sender
    |--------------------------------------------------------------------------
    |
    | Who sends the parcels: the client id of the contract and the office the
    | parcels are dropped at. Speedy prices a delivery from there.
    |
    */
    'sender' => [
        'client_id' => env('SPEEDY_SENDER_CLIENT_ID'),
        'office_id' => env('SPEEDY_SENDER_OFFICE_ID'),
        'name'      => env('SPEEDY_SENDER_NAME'),
        'phone'     => env('SPEEDY_SENDER_PHONE'),
    ],

    'package_weight'      => env('SPEEDY_PACKAGE_WEIGHT', 1),
    'package_description' => env('SPEEDY_PACKAGE_DESCRIPTION', 'Parcel'),
];
