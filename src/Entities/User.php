<?php

namespace App\Entities;

class User
{
    public int $id;
    public string $email;
    private string $password;
    public string $role;
    public ?string $twofa_secret;
    public string $twofa_method;
    public bool $twofa_enabled;
    public ?string $phone_number;

    private function __construct() {}

    public static function fromArray(array $data): self
    {
        $user = new self();
        $user->id = $data['id'];
        $user->email = $data["email"];
        $user->password = $data['password'];
        $user->role = $data["role"] ?? 'user';
        $user->twofa_secret = $data['twofa_secret'] ?? null;
        $user->twofa_method = $data['twofa_method'] ?? 'none';
        $user->twofa_enabled = (bool)($data['twofa_enabled'] ?? false);
        $user->phone_number = $data['phone_number'] ?? null;

        return $user;
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function has2FAEnabled(): bool
    {
        return $this->twofa_enabled && $this->twofa_method !== 'none';
    }
}
