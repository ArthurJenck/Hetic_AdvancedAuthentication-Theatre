<?php

namespace App\Controllers;

use App\Auth\JWT;
use App\Auth\JWTPayload;
use App\Auth\RefreshTokenManager;
use App\Repositories\UserRepository;
use App\Services\UserService;
use App\Services\Auth\TwoFactorAuthenticationService;

class AuthController
{
    private UserRepository $userRepository;
    private UserService $userService;
    private TwoFactorAuthenticationService $twoFactorService;
    private JWT $jwt;
    private RefreshTokenManager $refreshTokenManager;

    public function __construct(
        UserRepository $userRepository,
        UserService $userService,
        TwoFactorAuthenticationService $twoFactorService
    ) {
        $this->userRepository = $userRepository;
        $this->userService = $userService;
        $this->twoFactorService = $twoFactorService;
        $this->jwt = JWT::getInstance();
        $this->refreshTokenManager = new RefreshTokenManager();
    }

    public function showLogin(): void
    {
        require __DIR__ . '/../../views/login.php';
    }

    public function showRegister(): void
    {
        $error = $_GET['error'] ?? null;
        require __DIR__ . '/../../views/register.php';
    }

    public function register(): void
    {
        if (!verifyCsrf()) {
            header("Location: " . url('/register?error=csrf_invalid'));
            exit;
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: " . url('/register?error=invalid_email'));
            exit;
        }

        if (strlen($password) < 6) {
            header("Location: " . url('/register?error=weak_password'));
            exit;
        }

        if ($password !== $confirmPassword) {
            header("Location: " . url('/register?error=passwords_mismatch'));
            exit;
        }

        $user = $this->userService->createUser($email, $password);

        if (!$user) {
            header("Location: " . url('/register?error=email_exists'));
            exit;
        }

        session_start();
        $_SESSION['pending_2fa_setup_user_id'] = $user->id;
        $_SESSION['can_skip_2fa'] = true;

        header("Location: " . url('/setup-2fa-choice'));
        exit;
    }

    public function login(): void
    {
        if (!verifyCsrf()) {
            header("Location: " . url('/login'));
            exit;
        }

        $email = $_POST["email"] ?? '';
        $password = $_POST['password'] ?? '';

        $user = $this->userService->authenticate($email, $password);

        if (!$user) {
            header("Location: " . url('/login?error=invalid_credentials'));
            exit;
        }

        session_start();

        if ($user->has2FAEnabled()) {
            $_SESSION['verify_2fa_user_id'] = $user->id;

            if (in_array($user->twofa_method, ['email', 'sms'])) {
                $result = $this->twoFactorService->sendVerificationCode($user);

                if ($result['success']) {
                    $_SESSION['sms_toast_message'] = $result['message'];
                }
            }

            header("Location: " . url('/verify-2fa'));
            exit;
        }

        $this->createSession($user);
        header("Location: " . url('/'));
    }


    public function showVerify2FA(): void
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

        $toastMessage = $_SESSION['sms_toast_message'] ?? null;
        unset($_SESSION['sms_toast_message']);
        $error = $_GET['error'] ?? null;

        if ($user->twofa_method === 'totp') {
            require __DIR__ . '/../../views/verify-2fa-totp.php';
        } else {
            require __DIR__ . '/../../views/verify-2fa-code.php';
        }
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

        $isValid = $this->twoFactorService->verifyCode($user, $code);

        if (!$isValid) {
            header('Location: ' . url('/verify-2fa?error=invalid_code'));
            exit;
        }

        unset($_SESSION['verify_2fa_user_id']);

        $this->createSession($user);
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

    private function createSession($user): void
    {
        $payload = new JWTPayload(
            userId: $user->id,
            email: $user->email,
            role: $user->role
        );

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
    }
}
