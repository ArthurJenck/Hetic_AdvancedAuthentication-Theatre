<?php

namespace App\Controllers;

use App\Middleware\IsGranted;
use App\Repositories\UserRepository;
use App\Services\Auth\TwoFactorAuthenticationService;
use App\Services\UserService;

class TwoFactorController
{
    private TwoFactorAuthenticationService $twoFactorService;
    private UserRepository $userRepository;
    private UserService $userService;

    public function __construct(
        TwoFactorAuthenticationService $twoFactorService,
        UserRepository $userRepository,
        UserService $userService
    ) {
        $this->twoFactorService = $twoFactorService;
        $this->userRepository = $userRepository;
        $this->userService = $userService;
    }

    public function showMethodChoice(): void
    {
        session_start();

        if (!isset($_SESSION['pending_2fa_setup_user_id'])) {
            header("Location: " . url("/login"));
            exit;
        }

        $methods = $this->twoFactorService->getAvailableMethods();
        $canSkip = $_SESSION['can_skip_2fa'] ?? false;

        require __DIR__ . '/../../views/setup-2fa-choice.php';
    }

    public function processMethodChoice(): void
    {
        session_start();

        if (!isset($_SESSION['pending_2fa_setup_user_id'])) {
            header("Location: " . url("/login"));
            exit;
        }

        $userId = $_SESSION['pending_2fa_setup_user_id'];
        $choice = $_POST['method'] ?? 'skip';

        if ($choice === 'skip' && ($_SESSION['can_skip_2fa'] ?? false)) {
            unset($_SESSION['pending_2fa_setup_user_id'], $_SESSION['can_skip_2fa']);

            // Connexion directe
            $user = $this->userRepository->findById($userId);
            $this->loginUser($user);
            exit;
        }

        $_SESSION['chosen_2fa_method'] = $choice;

        if ($choice === 'email') {
            $user = $this->userRepository->findById($userId);
            $emailMethod = $this->twoFactorService->getMethod('email');
            $result = $emailMethod->sendCode($user);
            $_SESSION['2fa_code_sent'] = $result['success'];
        }

        header("Location: " . url("/setup-2fa/{$choice}"));
        exit;
    }

    public function setupTOTP(): void
    {
        session_start();

        if (!isset($_SESSION['pending_2fa_setup_user_id'])) {
            header("Location: " . url("/login"));
            exit;
        }

        $userId = $_SESSION['pending_2fa_setup_user_id'];
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            header('Location: ' . url('/login'));
            exit;
        }

        $result = $this->twoFactorService->setupMethod($user, 'totp');

        if (!$result['success']) {
            header('Location: ' . url('/setup-2fa-choice?error=' . urlencode($result['message'])));
            exit;
        }

        $qrCodeDataUri = $result['data']['qr_code'];
        $error = $_GET['error'] ?? null;

        require __DIR__ . '/../../views/setup-2fa-totp.php';
    }

    public function setupEmail(): void
    {
        session_start();

        if (!isset($_SESSION['pending_2fa_setup_user_id'])) {
            header("Location: " . url("/login"));
            exit;
        }

        $userId = $_SESSION['pending_2fa_setup_user_id'];
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            header('Location: ' . url('/login'));
            exit;
        }

        $error = $_GET['error'] ?? null;
        $message = $_SESSION['2fa_code_sent'] ?? false ? 'Un code de vérification a été envoyé' : null;

        require __DIR__ . '/../../views/setup-2fa-email.php';
    }

    public function setupSMS(): void
    {
        session_start();

        if (!isset($_SESSION['pending_2fa_setup_user_id'])) {
            header("Location: " . url("/login"));
            exit;
        }

        $error = $_GET['error'] ?? null;

        require __DIR__ . '/../../views/setup-2fa-sms.php';
    }

    public function setupSMSPhone(): void
    {
        session_start();

        if (!isset($_SESSION['pending_2fa_setup_user_id'])) {
            header("Location: " . url("/login"));
            exit;
        }

        $userId = $_SESSION['pending_2fa_setup_user_id'];
        $user = $this->userRepository->findById($userId);
        $phoneNumber = $_POST['phone_number'] ?? '';

        if (empty($phoneNumber)) {
            header('Location: ' . url('/setup-2fa/sms?error=missing_phone'));
            exit;
        }

        $this->userService->updatePhoneNumber($userId, $phoneNumber);
        $user = $this->userRepository->findById($userId);

        $result = $this->twoFactorService->setupMethod($user, 'sms', ['phone_number' => $phoneNumber]);

        if (!$result['success']) {
            header('Location: ' . url('/setup-2fa/sms?error=' . urlencode($result['message'])));
            exit;
        }

        $smsMethod = $this->twoFactorService->getMethod('sms');
        $sendResult = $smsMethod->sendCode($user);
        $_SESSION['sms_toast_message'] = $sendResult['message'] ?? '';

        header('Location: ' . url('/setup-2fa/sms/verify'));
        exit;
    }

    public function verifySMSCode(): void
    {
        session_start();

        if (!isset($_SESSION['pending_2fa_setup_user_id'])) {
            header("Location: " . url("/login"));
            exit;
        }

        $toastMessage = $_SESSION['sms_toast_message'] ?? null;
        unset($_SESSION['sms_toast_message']);
        $error = $_GET['error'] ?? null;

        require __DIR__ . '/../../views/setup-2fa-code.php';
    }

    public function completeSetup(): void
    {
        session_start();

        if (!isset($_SESSION['pending_2fa_setup_user_id'])) {
            header("Location: " . url("/login"));
            exit;
        }

        $userId = $_SESSION['pending_2fa_setup_user_id'];
        $method = $_SESSION['chosen_2fa_method'] ?? null;
        $user = $this->userRepository->findById($userId);
        $code = $_POST['code'] ?? '';

        if (!$user || !$method) {
            header('Location: ' . url("/login"));
            exit;
        }

        if (empty($code)) {
            header("Location: " . url("/setup-2fa/{$method}?error=missing_code"));
            exit;
        }

        $methodInstance = $this->twoFactorService->getMethod($method);
        if (!$methodInstance) {
            header('Location: ' . url("/login"));
            exit;
        }
        $isValid = $methodInstance->verifyCode($user, $code);

        if (!$isValid) {
            header("Location: " . url("/setup-2fa/{$method}?error=invalid_code"));
            exit;
        }

        $this->userService->enable2FA($userId, $method);
        unset($_SESSION['pending_2fa_setup_user_id'], $_SESSION['chosen_2fa_method'], $_SESSION['can_skip_2fa']);

        $user = $this->userRepository->findById($userId);
        $this->loginUser($user);
    }

    #[IsGranted]
    public function manage(): void
    {
        $user = getCurrentUser();
        $methods = $this->twoFactorService->getAvailableMethods();

        require __DIR__ . '/../../views/profile-2fa-management.php';
    }

    #[IsGranted]
    public function enableFromProfile(): void
    {
        session_start();
        $user = getCurrentUser();

        $_SESSION['pending_2fa_setup_user_id'] = $user->id;
        $_SESSION['can_skip_2fa'] = false;
        $_SESSION['return_to_profile'] = true;

        header('Location: ' . url('/setup-2fa-choice'));
        exit;
    }

    #[IsGranted]
    public function changeMethod(): void
    {
        session_start();
        $user = getCurrentUser();

        $_SESSION['pending_2fa_setup_user_id'] = $user->id;
        $_SESSION['can_skip_2fa'] = false;
        $_SESSION['changing_method'] = true;

        header('Location: ' . url('/setup-2fa-choice'));
        exit;
    }

    #[IsGranted]
    public function disable(): void
    {
        $user = getCurrentUser();
        $this->userService->disable2FA($user->id);

        header('Location: ' . url('/profile/2fa?success=disabled'));
        exit;
    }

    private function loginUser($user): void
    {
        $jwt = \App\Auth\JWT::getInstance();
        $refreshTokenManager = new \App\Auth\RefreshTokenManager();

        $payload = new \App\Auth\JWTPayload(
            userId: $user->id,
            email: $user->email,
            role: $user->role
        );

        $accessToken = $jwt->generateJWT($payload);
        $refreshToken = $refreshTokenManager->create($user->id);

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

        header('Location: ' . url('/'));
    }
}
