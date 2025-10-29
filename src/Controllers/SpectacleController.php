<?php

namespace App\Controllers;

use App\Middleware\IsGranted;
use App\Repositories\SpectacleRepository;

class SpectacleController
{
    private SpectacleRepository $spectacleRepository;

    public function __construct()
    {
        $this->spectacleRepository = new SpectacleRepository();
    }

    public function list(): void
    {
        $spectacles = $this->spectacleRepository->findAll();
        $user = getCurrentUser();
        require __DIR__ . '/../../views/spectacles/list.php';
    }

    public function show(string $id): void
    {
        $spectacle = $this->spectacleRepository->findById((int)$id);

        if (!$spectacle) {
            http_response_code(404);
            echo 'Spectacle non trouvé';
            return;
        }

        $user = getCurrentUser();
        require __DIR__ . '/../../views/spectacles/detail.php';
    }

    #[IsGranted('admin')]
    public function showCreate(): void
    {
        $user = getCurrentUser();
        require __DIR__ . '/../../views/spectacles/create.php';
    }

    #[IsGranted('admin')]
    public function create(): void
    {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $date = $_POST['date'] ?? '';
        $seats = (int)($_POST['available_seats'] ?? 0);

        if (empty($title) || empty($date) || $seats <= 0) {
            $_SESSION['error'] = "Tous les champs sont requis";
            header('Location: ' . url('/spectacles/create'));
            exit;
        }

        $this->spectacleRepository->create($title, $description, $date, $seats);
        header('Location: ' . url('/spectacles'));
    }
}
