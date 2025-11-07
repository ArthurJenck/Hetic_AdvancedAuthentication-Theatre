<?php

namespace App\Controllers;

use App\Middleware\IsGranted;
use App\Repositories\ReservationRepository;
use App\Repositories\SpectacleRepository;

class ReservationController
{
    private ReservationRepository $reservationRepository;
    private SpectacleRepository $spectacleRepository;

    public function __construct()
    {
        $this->reservationRepository = new ReservationRepository();
        $this->spectacleRepository = new SpectacleRepository();
    }

    #[IsGranted]
    public function create(): void
    {
        $spectacleId = (int)($_POST['spectacle_id'] ?? 0);
        $user = getCurrentUser();
        $userId = $user->id;

        if (!$spectacleId) {
            http_response_code(400);
            echo 'Spectacle non spécifié';
            return;
        }

        $spectacle = $this->spectacleRepository->findById($spectacleId);

        if (!$spectacle || !$spectacle->hasAvailableSeats()) {
            header("Location: " . url("/spectacles/$spectacleId"));
            exit;
        }

        $this->reservationRepository->create($userId, $spectacleId);
        $this->spectacleRepository->decrementSeats($spectacleId);

        header('Location: ' . url('/profile'));
    }

    #[IsGranted]
    public function myReservations(): void
    {
        $user = getCurrentUser();
        $userId = $user->id;
        $reservations =  $this->reservationRepository->findByUserId($userId);

        require __DIR__ . '/../../views/profile.php';
    }
}
