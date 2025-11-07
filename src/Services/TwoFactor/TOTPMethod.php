<?php

namespace App\Services\TwoFactor;

use App\Entities\User;
use App\Repositories\UserRepository;
use Da\TwoFA\Manager;
use Da\TwoFA\Service\QrCodeDataUriGeneratorService;
use Da\TwoFA\Service\TOTPSecretKeyUriGeneratorService;

class TOTPMethod implements TwoFactorMethodInterface
{
    private Manager $manager;
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->manager = new Manager();
        $this->userRepository = $userRepository;
    }

    public function sendCode(User $user): array
    {
        return [
            'success' => true,
            'message' => 'Utilisez votre application d\'authentification pour obtenir le code',
            'data' => null
        ];
    }

    public function verifyCode(User $user, string $code): bool
    {
        if (empty($user->twofa_secret)) {
            return false;
        }

        return $this->manager->verify($code, $user->twofa_secret);
    }

    public function setup(User $user, array $data = []): array
    {
        if (empty($user->twofa_secret)) {
            $secret = $this->manager->generateSecretKey();
            $this->userRepository->updateTwoFASecret($user->id, $secret);
            $user = $this->userRepository->findById($user->id);
        }

        $totpUri = (new TOTPSecretKeyUriGeneratorService(
            'Le Théâtre d\'Arthur Jenck',
            $user->email,
            $user->twofa_secret
        ))->run();

        $qrCodeDataUri = (new QrCodeDataUriGeneratorService($totpUri))->run();

        return [
            'success' => true,
            'message' => 'Scannez le QR code avec votre application d\'authentification',
            'data' => [
                'qr_code' => $qrCodeDataUri,
                'secret' => $user->twofa_secret,
            ]
        ];
    }

    public function getMethodName(): string
    {
        return 'totp';
    }
}
