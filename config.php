<?php

return [
    'db' => [
        "host" => 'localhost',
        'dbname' => "aa_theatre",
        "user" => 'root',
        'password' => ''
    ],
    'jwt' => [
        'secret' => 'EEF5DBE1F22327DD78F4A9B5613A5',
        'access_token_expiration' => 60 * 15,
        'refresh_token_expiration' => 60 * 60 * 24 * 30,
    ]
];
