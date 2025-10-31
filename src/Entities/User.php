<?php

namespace App\Entities;

class User
{
    public int $id;
    public string $email;
    private string $password;
    public string $role;
    public ?string $twofa_secret;

    private function __construct() {}

    public static function fromArray(array $data): self
    {
        $user = new self();
        $user->id = $data['id'];
        $user->email = $data["email"];
        $user->password = $data['password'];
        $user->role = $data["role"] ?? 'user';
        $user->twofa_secret = $data['twofa_secret'] ?? null;

        return $user;
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }
}
