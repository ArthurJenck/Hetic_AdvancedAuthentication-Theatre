<?php

use Da\TwoFA\Manager;

$twoFaSecret = (new Manager())->generateSecretKey();

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
    ],
    'twoFa' => [
        'secret' => $twoFaSecret,
    ],
    'email' => [
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_auth' => true,
        'smtp_username' => 'arthurjenckdev@gmail.com',
        'smtp_password' => 'svqd jgzq nksu rhjl',
        'smtp_secure' => 'tls',
        'from_email' => 'arthurjenckdev@gmail.com',
        'from_name' => "Le Théâtre d'Arthur Jenck",
    ]
];
