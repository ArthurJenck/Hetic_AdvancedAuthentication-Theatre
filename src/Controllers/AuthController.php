<?php

namespace App\Controllers;

use App\Auth\JWT;
use App\Auth\JWTPayload;
use App\Auth\RefreshTokenManager;
use App\Repositories\UserRepository;

class AuthController
{
    private UserRepository $userRepository;
    private JWT $jwt;
    private RefreshTokenManager $refreshTokenManager;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $this->jwt = JWT::getInstance();
        $this->refreshTokenManager = new RefreshTokenManager();
    }

    public function showLogin(): void
    {
        require __DIR__ . '/../../views/login.php';
    }

    public function login(): void
    {
        $email = $_POST["email"] ?? '';
        $password = $_POST['password'] ?? '';

        $user = $this->userRepository->findByEmail($email);

        if (!$user || !$user->verifyPassword($password)) {
            header("Location: " . url('/login'));
            exit;
        }

        $payload = new JWTPayload(userId: $user->id, email: $user->email, role: $user->role);

        $accessToken = $this->jwt->generateJWT($payload);
        $refreshToken = $this->refreshTokenManager->create($user->id);

        setcookie("access_token", $accessToken, [
            'expires' => $payload->exp,
            "path" => '/',
            'httponly' => true,
            'secure' => true,
            'samesite' => 'Strict'
        ]);


        $config = require __DIR__ . '/../../config.php';
        setcookie('refresh_token', $refreshToken, [
            'expires' => time() + $config['jwt']['refresh_token_expiration'],
            'path' => '/',
            'httponly' => true,
            'secure' => true,
            'samesite' => 'Strict',
        ]);

        header("Location: " . url('/'));
    }

    public function refresh(): void
    {
        $refreshToken = $_COOKIE['refresh_token'] ?? '';

        if (!$refreshToken) {
            http_response_code(401);
            echo json_encode(['message' => 'Refresh token manquant']);
            exit;
        }

        $userId = $this->refreshTokenManager->validate($refreshToken);

        if (!$userId) {
            http_response_code(401);
            echo json_encode(['message' => 'Refresh token invalide ou expiré']);
            exit;
        }

        $user = $this->userRepository->findById($userId);

        if (!$user) {
            http_response_code(401);
            echo json_encode(['message' => 'Utilisateur introuvable']);
            exit;
        }

        $payload = new JWTPayload($user->id, $user->email, $user->role);
        $newAccessToken = $this->jwt->generateJWT($payload);

        setcookie('access_token', $newAccessToken, [
            'expires' => $payload->exp,
            'path' => '/',
            'httponly' => true,
            'secure' => true,
            'samesite' => 'Strict'
        ]);

        echo json_encode(['message' => 'Token renouvelé']);
    }

    public function logout(): void
    {
        $refreshToken = $_COOKIE['refresh_token'] ?? '';

        if ($refreshToken) {
            $this->refreshTokenManager->revoke($refreshToken);
        }

        setcookie('access_token', '', time() - 3600, '/');
        setcookie('refresh_token', '', time() - 3600, '/');

        header('Location: ' . url('/'));
    }
}
