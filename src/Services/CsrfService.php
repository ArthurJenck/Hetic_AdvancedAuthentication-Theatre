<?php

namespace App\Services;

class CsrfService
{
    public static function generateToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        
        return $token;
    }

    public static function validateToken(string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $sessionToken = $_SESSION['csrf_token'] ?? '';
        
        return !empty($token) && hash_equals($sessionToken, $token);
    }

    public static function getTokenField(): string
    {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}

