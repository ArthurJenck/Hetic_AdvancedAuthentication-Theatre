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
            $userRepository = new App\Repositories\UserRepository;
            return $userRepository->findById($payload->sub);
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

    return $user;
}

function setFlashMessage(string $type, string $message): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage(): ?array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $flash = $_SESSION['flash_message'] ?? null;
    unset($_SESSION['flash_message']);
    
    return $flash;
}

function displayFlashMessage(): void
{
    $flash = getFlashMessage();
    
    if ($flash) {
        $type = htmlspecialchars($flash['type']);
        $message = htmlspecialchars($flash['message']);
        
        $colors = [
            'success' => ['bg' => '#d4edda', 'border' => '#28a745', 'text' => '#155724'],
            'error' => ['bg' => '#f8d7da', 'border' => '#dc3545', 'text' => '#721c24'],
            'info' => ['bg' => '#d1ecf1', 'border' => '#17a2b8', 'text' => '#0c5460'],
            'warning' => ['bg' => '#fff3cd', 'border' => '#ffc107', 'text' => '#856404'],
        ];
        
        $color = $colors[$type] ?? $colors['info'];
        
        echo '<div style="position: fixed; top: 20px; left: 20px; z-index: 9999; max-width: 400px; background: ' . $color['bg'] . '; color: ' . $color['text'] . '; border-left: 4px solid ' . $color['border'] . '; padding: 15px 40px 15px 15px; border-radius: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); animation: slideIn 0.3s ease;">';
        echo $message;
        echo '</div>';
        echo '<style>@keyframes slideIn { from { transform: translateX(-120%); } to { transform: translateX(0); } }</style>';
    }
}

function verifyCsrf(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return \App\Services\CsrfService::validateToken($token);
}

function csrfField(): string
{
    return \App\Services\CsrfService::getTokenField();
}