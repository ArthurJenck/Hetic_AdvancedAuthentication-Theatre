<?php


function url(string $path): string
{
    return ($_SERVER['BASE_PATH'] ?? '') . $path;
}

function getCurrentUser(): ?object
{
    $accessToken = $_COOKIE['access_token'] ?? null;
    $jwt = \App\Auth\JWT::getInstance();

    if ($accessToken) {
        $payload = $jwt->validateJWT($accessToken);

        if ($payload) {
            return $payload;
        }
    }

    $refreshToken = $_COOKIE['refresh_token'] ?? null;

    if (!$refreshToken) {
        return null;
    }

    $refreshTokenManager = new \App\Auth\RefreshTokenManager();
    $userId = $refreshTokenManager->validate($refreshToken);

    if (!$userId) {
        return null;
    }

    $userRepository = new \App\Repositories\UserRepository();
    $user = $userRepository->findById($userId);

    if (!$user) {
        return null;
    }

    $payload = new \App\Auth\JWTPayload($user->id, $user->email, $user->role);
    $newAccessToken = $jwt->generateJWT($payload);

    setcookie('access_token', $newAccessToken, [
        'expires' => $payload->exp,
        'path' => '/',
        'httponly' => true,
        'secure' => true,
        'samesite' => 'Strict'
    ]);

    return $payload;
}
