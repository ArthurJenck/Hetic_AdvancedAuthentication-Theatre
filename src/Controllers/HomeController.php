<?php

namespace App\Controllers;

class HomeController
{
    public function index(): void
    {
        $user = getCurrentUser();
        require __DIR__ . "/../../views/home.php";
    }
}
