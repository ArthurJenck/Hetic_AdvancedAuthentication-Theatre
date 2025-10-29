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
}
