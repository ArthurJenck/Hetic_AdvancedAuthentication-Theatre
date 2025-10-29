<?php

namespace App\Repositories;

use App\Core\Database;
use App\Entities\Spectacle;
use PDO;

class SpectacleRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM spectacles ORDER BY date DESC");
        $data = $stmt->fetchAll();

        return array_map(fn($row) => Spectacle::fromArray($row), $data);
    }

    public function findById(int $id): ?Spectacle
    {
        $stmt = $this->pdo->prepare("SELECT * FROM spectacles WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        return $data ? Spectacle::fromArray($data) : null;
    }

    public function create(string $title, string $description, string $date, int $seats): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO spectacles (title, description, date, available_seats) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$title, $description, $date, $seats]);

        return (int)$this->pdo->lastInsertId();
    }

    public function decrementSeats(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE spectacles SET available_seats = available_seats - 1 WHERE id = ? AND available_seats > 0"
        );
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }
}
