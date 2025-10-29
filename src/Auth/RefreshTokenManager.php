<?php

namespace App\Auth;

use App\Core\Database;
use PDO;

class RefreshTokenManager
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function create(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);

        $config = require __DIR__ . '/../../config.php';
        $expiresAt = date("Y-m-d H:i:s", time() + $config['jwt']['refresh_token_expiration']);

        $stmt = $this->pdo->prepare(
            "INSERT INTO refresh_tokens (user_id, token_hash, expires_at) VALUES (?,?,?)"
        );
        $stmt->execute([$userId, $hash, $expiresAt]);

        return $token;
    }

    public function validate(string $token): ?int
    {
        $hash = hash('sha256', $token);

        $stmt = $this->pdo->prepare("SELECT user_id, expires_at FROM refresh_tokens WHERE token_hash = ?");
        $stmt->execute([$hash]);
        $row = $stmt->fetch();

        if (!$row || strtotime($row['expires_at']) < time()) {
            return null;
        }

        return (int)$row['user_id'];
    }

    public function revoke(string $token): void
    {
        $hash = hash('sha256', $token);
        $stmt = $this->pdo->prepare("DELETE FROM refresh_tokens WHERE token_hash = ?");
        $stmt->execute([$hash]);
    }

    public function revokeAllForUser(int $userId): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM refresh_tokens WHERE user_id = ?");
        $stmt->execute([$userId]);
    }

    public function cleanExpired(): void
    {
        $this->pdo->exec("DELETE FROM refresh_tokens WHERE expires_at < NOW()");
    }
}
