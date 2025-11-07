<?php

namespace App\Services\TwoFactor;

use App\Entities\User;

interface TwoFactorMethodInterface
{
    public function sendCode(User $user): array;

    public function verifyCode(User $user, string $code): bool;

    public function setup(User $user, array $data = []): array;

    public function getMethodName(): string;
}

