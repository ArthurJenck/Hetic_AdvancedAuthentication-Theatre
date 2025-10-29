<?php

namespace App\Controllers;

use App\Auth\JWT;
use App\Auth\JTWPayload;
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
        require __DIR__ . '/../../view/login.php';
    }

    public function login(): void
    {
        $email = $_POST["email"] ?? '';
        $password = $_POST['passwordd'] ?? '';

        $user = $this->userRepository->findByEmail($email);

        if (!$user || !$user->verifyPassword($password)) {
            $_SESSION['error'] = 'Identifiants incorrects';
            header("Location: /login");
            exit;
        }

        $payload = new JTWPayload(userId: $user->id, email: $user->email, role: $user->role);

        $accessToken = $this->jwt->generateJWT($payload);
        $refreshToken = $this->refreshTokenManager->create($user->id);

        setcookie("token", $accessToken, [
            'expires' => $payload->exp,
            "path" => '/',
            'httponly' => true,
            'secure' => true,
            'samesite' => 'Strict'
        ]);


        require __DIR__ . '/../../config/config.php';
        setcookie('refresh_token', $refreshToken, [
            'expires' => 60 * 60 * 24 * 30,
            'path' => '/',
            'httponly' => true,
            'secure' => true,
            'samesite' => 'Strict',
        ]);

        header("Location: /");
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

        $payload = new JTWPayload($user->id, $user->email, $user->role);
        $newAccessToken = $this->jwt->generateJWT($payload);

        setcookie('token', $newAccessToken, [
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

        setcookie('token', '', time() - 3600, '/');
        setcookie('refresh_token', '', time() - 3600, '/');

        session_destroy();
        header('Location: /');
    }
}
