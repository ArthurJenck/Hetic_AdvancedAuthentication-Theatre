<?php

namespace App\Repositories;

use App\Core\Database;
use App\Entities\User;
use PDO;

class UserRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $data = $stmt->fetch();

        return $data ? User::fromArray($data) : null;
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $data =  $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $data ? User::fromArray($data) : null;
    }

    public function create(string $email, string $hashedPassword, string $role = 'user'): ?int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO users (email, password, role, twofa_method, twofa_enabled) VALUES (?, ?, ?, 'none', FALSE)"
        );
        
        if ($stmt->execute([$email, $hashedPassword, $role])) {
            return (int)$this->pdo->lastInsertId();
        }
        
        return null;
    }

    public function updateTwoFASecret(int $userId, string $secret): bool
    {
        $stmt = $this->pdo->prepare("UPDATE users SET twofa_secret = ? WHERE id = ?");
        return $stmt->execute([$secret, $userId]);
    }

    public function update2FASettings(int $userId, string $method, bool $enabled): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE users SET twofa_method = ?, twofa_enabled = ? WHERE id = ?"
        );
        return $stmt->execute([$method, $enabled, $userId]);
    }

    public function update2FAMethod(int $userId, string $method): bool
    {
        $stmt = $this->pdo->prepare("UPDATE users SET twofa_method = ? WHERE id = ?");
        return $stmt->execute([$method, $userId]);
    }

    public function updatePhoneNumber(int $userId, string $phoneNumber): bool
    {
        $stmt = $this->pdo->prepare("UPDATE users SET phone_number = ? WHERE id = ?");
        return $stmt->execute([$phoneNumber, $userId]);
    }
}

