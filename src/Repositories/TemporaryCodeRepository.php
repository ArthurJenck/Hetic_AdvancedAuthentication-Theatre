<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class TemporaryCodeRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function create(int $userId, string $code, string $method, string $expiresAt): int
    {
        $this->invalidateOldCodes($userId, $method);

        $stmt = $this->pdo->prepare(
            "INSERT INTO temporary_codes (user_id, code, method, expires_at) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$userId, $code, $method, $expiresAt]);
        
        return (int)$this->pdo->lastInsertId();
    }

    public function verify(int $userId, string $code, string $method): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, expires_at, used FROM temporary_codes 
             WHERE user_id = ? AND code = ? AND method = ? 
             ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->execute([$userId, $code, $method]);
        $row = $stmt->fetch();

        if (!$row) {
            return false;
        }

        if ($row['used']) {
            return false;
        }

        if (strtotime($row['expires_at']) < time()) {
            return false;
        }

        $this->markAsUsed($row['id']);

        return true;
    }

    private function markAsUsed(int $codeId): void
    {
        $stmt = $this->pdo->prepare("UPDATE temporary_codes SET used = TRUE WHERE id = ?");
        $stmt->execute([$codeId]);
    }

    private function invalidateOldCodes(int $userId, string $method): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE temporary_codes SET used = TRUE 
             WHERE user_id = ? AND method = ? AND used = FALSE"
        );
        $stmt->execute([$userId, $method]);
    }

    public function countRecentCodes(int $userId, string $method, int $minutes = 10): int
    {
        $since = date('Y-m-d H:i:s', time() - ($minutes * 60));
        
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) as count FROM temporary_codes 
             WHERE user_id = ? AND method = ? AND created_at >= ?"
        );
        $stmt->execute([$userId, $method, $since]);
        $row = $stmt->fetch();
        
        return (int)($row['count'] ?? 0);
    }

    public function cleanExpired(): int
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM temporary_codes WHERE expires_at < NOW()"
        );
        $stmt->execute();
        
        return $stmt->rowCount();
    }

    public function getLastCode(int $userId, string $method): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM temporary_codes 
             WHERE user_id = ? AND method = ? 
             ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->execute([$userId, $method]);
        $row = $stmt->fetch();
        
        return $row ?: null;
    }
}

