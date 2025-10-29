<?php

namespace App\Controllers;

class HomeController
{
    public function index(): void
    {
        $user = $_SESSION["user"] ?? null;
        require __DIR__ . "/../../views/home.php";
    }
}
