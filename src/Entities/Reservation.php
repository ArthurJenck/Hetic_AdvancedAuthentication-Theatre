<?php

namespace App\Entities;

class Reservation
{
    public int $id;
    public int $user_id;
    public int $spectacle_id;
    public ?string $spectacle_title = null;
    public ?string $spectacle_date = null;

    public function __construct() {}

    public static function fromArray(array $data): self
    {
        $reservation = new self();
        $reservation->id = $data['id'];
        $reservation->user_id = $data['user_id'];
        $reservation->spectacle_id = $data["spectacle_id"];
        $reservation->spectacle_title = $data['title'] ?? null;
        $reservation->spectacle_date = $data['date'] ?? null;

        return $reservation;
    }
}
