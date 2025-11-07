<?php

namespace App\Services;

use App\Entities\User;
use App\Repositories\UserRepository;

class UserService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function createUser(string $email, string $password, string $role = 'user'): ?User
    {
        if ($this->userRepository->findByEmail($email)) {
            return null;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $userId = $this->userRepository->create($email, $hashedPassword, $role);

        if (!$userId) {
            return null;
        }

        return $this->userRepository->findById($userId);
    }

    public function authenticate(string $email, string $password): ?User
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !$user->verifyPassword($password)) {
            return null;
        }

        return $user;
    }

    public function enable2FA(int $userId, string $method): bool
    {
        return $this->userRepository->update2FASettings($userId, $method, true);
    }

    public function disable2FA(int $userId): bool
    {
        return $this->userRepository->update2FASettings($userId, 'none', false);
    }

    public function change2FAMethod(int $userId, string $newMethod): bool
    {
        return $this->userRepository->update2FAMethod($userId, $newMethod);
    }

    public function updatePhoneNumber(int $userId, string $phoneNumber): bool
    {
        return $this->userRepository->updatePhoneNumber($userId, $phoneNumber);
    }

    public function has2FAEnabled(User $user): bool
    {
        return $user->twofa_enabled && $user->twofa_method !== 'none';
    }
}

