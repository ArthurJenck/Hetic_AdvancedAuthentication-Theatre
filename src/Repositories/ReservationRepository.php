<?php

namespace App\Repositories;

use App\Core\Database;
use App\Entities\Reservation;
use PDO;

class ReservationRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function create(int $userId, int $spectacleId): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO reservations (user_id, spectacle_id) VALUES (?, ?)");
        $stmt->execute([$userId, $spectacleId]);

        return (int)$this->pdo->lastInsertId();
    }

    public function findByUserId(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT r.*, s.title, s.date FROM reservations r JOIN spectacles s ON r.spectacle_id = s.id WHERE r.user_id = ? ORDER BY s.date DESC");
        $stmt->execute([$userId]);
        $data = $stmt->fetchAll();

        return array_map(fn($row) => Reservation::fromArray($row), $data);
    }
}
