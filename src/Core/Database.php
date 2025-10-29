<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . "/../../config.php";
            $db = $config["db"];

            try {
                self::$instance = new PDO(
                    "mysql:host={$db['host']};dbname={$db['dbname']};charset=utf8mb4",
                    $db['user'],
                    $db['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                die("Erreur de connexion à la base de données : " . $e->getMessage());
            }
        }
        return self::$instance;
    }

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
