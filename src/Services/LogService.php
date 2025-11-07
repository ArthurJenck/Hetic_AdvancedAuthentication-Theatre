<?php

namespace App\Services;

class LogService
{
    public function logSMS(string $phoneNumber, string $code): void
    {
    }

    public function logLoginAttempt(string $email, bool $success, string $reason = ''): void
    {
    }

    public function log2FAAction(int $userId, string $action, string $method, bool $success): void
    {
    }
}

