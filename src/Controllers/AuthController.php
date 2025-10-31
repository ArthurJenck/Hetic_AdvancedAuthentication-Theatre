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

        session_start();

        if (empty($user->twofa_secret)) {
            $_SESSION['setup_2fa_user_id'] = $user->id;
            header("Location: " . url('/setup-2fa'));
            exit;
        }

        $_SESSION['verify_2fa_user_id'] = $user->id;
        header("Location: " . url('/verify-2fa'));
    }

    public function showSetup2FA(): void
    {
        session_start();

        if (!isset($_SESSION['setup_2fa_user_id'])) {
            header("Location: " . url("/login"));
            exit;
        }

        $userId = $_SESSION["setup_2fa_user_id"];
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            header('Location: ' . url('/login'));
            exit;
        }

        if (empty($user->twofa_secret)) {
            $manager = new \Da\TwoFA\Manager();
            $secret = $manager->generateSecretKey();

            $this->userRepository->updateTwoFASecret($userId, $secret);

            $user = $this->userRepository->findById($userId);
        }

        $totpUri = (new \Da\TwoFA\Service\TOTPSecretKeyUriGeneratorService(
            'Theatre',
            $user->email,
            $user->twofa_secret
        ))->run();

        $qrCodeDataUri = (new \Da\TwoFA\Service\QrCodeDataUriGeneratorService($totpUri))->run();

        $isSetup = true;
        $formAction = url('/setup-2fa/complete');
        $error = $_GET['error'] ?? null;

        require __DIR__ . '/../../views/verify2fa.php';
    }

    public function complete2FASetup(): void
    {
        session_start();

        if (!isset($_SESSION["setup_2fa_user_id"])) {
            header("Location: " . url("/login"));
            exit;
        }

        $userId = $_SESSION['setup_2fa_user_id'];
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            header('Location: ' . url("/login"));
            exit;
        }

        $code = $_POST['code'] ?? '';

        if (empty($code)) {
            header('Location: ' . url('/setup-2fa?error=missing_code'));
            exit;
        }

        $manager = new \Da\TwoFA\Manager();
        $isValid = $manager->verify($code, $user->twofa_secret);

        if (!$isValid) {
            header('Location: ' . url('/setup-2fa?error=invalid_code'));
            exit;
        }

        unset($_SESSION['setup_2fa_user_id']);

        $payload = new JWTPayload(userId: $user->id, email: $user->email, role: $user->role);
        $accessToken = $this->jwt->generateJWT($payload);
        $refreshToken = $this->refreshTokenManager->create($user->id);

        setcookie("access_token", $accessToken, [
            'expires' => $payload->exp,
            'path' => '/',
            'httponly' => true,
            'secure' => true,
            'samesite' => 'Strict',
        ]);

        $config = require __DIR__ . '/../../config.php';
        setcookie('refresh_token', $refreshToken, [
            'expires' => time() + $config['jwt']['refresh_token_expiration'],
            'path' => '/',
            'httponly' => true,
            'secure' => true,
            'samesite' => 'Strict',
        ]);

        header('Location: ' . url("/"));
    }

    public function showVerify2FA(): void
    {
        session_start();

        if (!isset($_SESSION['verify_2fa_user_id'])) {
            header("Location: " . url('/login'));
            exit;
        }

        $isSetup = false;
        $qrCodeDataUri = null;
        $formAction = url('/verify-2fa');
        $error = $_GET['error'] ?? null;

        require __DIR__ . '/../../views/verify2fa.php';
    }

    public function verify2FA(): void
    {
        session_start();

        if (!isset($_SESSION['verify_2fa_user_id'])) {
            header("Location: " . url('/login'));
            exit;
        }

        $userId = $_SESSION['verify_2fa_user_id'];
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            header("Location: " . url('/login'));
            exit;
        }

        $code = $_POST['code'] ?? '';

        if (empty($code)) {
            header('Location: ' . url('/verify-2fa?error=missing_code'));
            exit;
        }

        $manager = new \Da\TwoFA\Manager();
        $isValid = $manager->verify($code, $user->twofa_secret);

        if (!$isValid) {
            header('Location: ' . url('/verify-2fa?error=invalid_code'));
            exit;
        }

        unset($_SESSION['verify_2fa_user_id']);

        $payload = new JWTPayload(userId: $user->id, email: $user->email, role: $user->role);
        $accessToken = $this->jwt->generateJWT($payload);
        $refreshToken = $this->refreshTokenManager->create($user->id);

        setcookie("access_token", $accessToken, [
            'expires' => $payload->exp,
            'path' => '/',
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
