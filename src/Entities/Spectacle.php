<?php

namespace App\Entities;

class Spectacle
{
    public int $id;
    public string $title;
    public ?string $description;
    public string $date;
    public int $available_seats;

    private function __construct() {}

    public static function fromArray(array $data): self
    {
        $spectacle = new self();
        $spectacle->id = $data['id'];
        $spectacle->title = $data["title"];
        $spectacle->description = $data["description"] ?? null;
        $spectacle->date = $data["date"];
        $spectacle->available_seats = $data['available_seats'];

        return $spectacle;
    }

    public function hasAvailableSeats(): bool
    {
        return $this->available_seats > 0;
    }
}
