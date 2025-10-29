<?php

namespace App\Auth;

class JTWPayload
{
    public int $sub;
    public string $email;
    public ?string $role;
    public int $iat;
    public int $exp;

    public function __construct(
        int $userId,
        string $email,
        ?string $role = null,
        ?int $expiresIn = null
    ) {
        $this->sub = $userId;
        $this->email = $email;
        $this->role = $role;
        $this->iat = time();

        $config = require __DIR__ . '/../../config.php';
        $defaultExp = $config['jwt']['access_token_expiration'] ?? 60 * 15;
        $this->exp = time() + ($expiresIn ?? $defaultExp);
    }

    public function toArray(): array
    {
        return array_filter(
            get_object_vars($this),
            fn($value) => $value !== null
        );
    }

    public function isExpired(): bool
    {
        return $this->exp < time();
    }
}
