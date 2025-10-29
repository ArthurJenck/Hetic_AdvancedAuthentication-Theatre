<?php


function url(string $path): string
{
    return ($_SERVER['BASE_PATH'] ?? '') . $path;
}

function getCurrentUser(): ?object
{
    if (isset($_SESSION['user'])) {
        return $_SESSION['user'];
    }

    $token = $_COOKIE['access_token'] ?? null;

    if (!$token) {
        return null;
    }

    $jwt = \App\Auth\JWT::getInstance();
    $payload = $jwt->validateJWT($token);

    if ($payload) {
        $_SESSION['user'] = $payload;
        return $payload;
    }

    return null;
}
