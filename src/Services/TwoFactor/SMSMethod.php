<?php

namespace App\Services\TwoFactor;

use App\Entities\User;
use App\Repositories\TemporaryCodeRepository;
use App\Services\LogService;

class SMSMethod implements TwoFactorMethodInterface
{
    private TemporaryCodeRepository $codeRepository;
    private LogService $logService;

    public function __construct(TemporaryCodeRepository $codeRepository, LogService $logService)
    {
        $this->codeRepository = $codeRepository;
        $this->logService = $logService;
    }

    public function sendCode(User $user): array
    {
        if (empty($user->phone_number)) {
            return [
                'success' => false,
                'message' => 'Aucun numéro de téléphone configuré',
                'data' => null
            ];
        }

        $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', time() + 300);
        $this->codeRepository->create($user->id, $code, 'sms', $expiresAt);
        $this->logService->logSMS($user->phone_number, $code);
        
        return [
            'success' => true,
            'message' => "SMS reçu : {$code} est votre code de vérification ArthurTheatre.",
            'data' => [
                'code' => $code, // Pour affichage dans le toast
                'phone' => $this->maskPhoneNumber($user->phone_number)
            ]
        ];
    }

    public function verifyCode(User $user, string $code): bool
    {
        return $this->codeRepository->verify($user->id, $code, 'sms');
    }

    public function setup(User $user, array $data = []): array
    {
        $phoneNumber = $data['phone_number'] ?? null;
        
        if (empty($phoneNumber)) {
            return [
                'success' => false,
                'message' => 'Numéro de téléphone requis',
                'data' => null
            ];
        }

        $phoneNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);
        
        if (strlen($phoneNumber) < 10) {
            return [
                'success' => false,
                'message' => 'Numéro de téléphone invalide',
                'data' => null
            ];
        }

        return [
            'success' => true,
            'message' => 'Numéro de téléphone enregistré. Un code de vérification va être envoyé.',
            'data' => [
                'phone_number' => $phoneNumber,
                'requires_verification' => true
            ]
        ];
    }

    public function getMethodName(): string
    {
        return 'sms';
    }

    private function maskPhoneNumber(string $phone): string
    {
        $length = strlen($phone);
        if ($length <= 4) {
            return $phone;
        }
        
        return substr($phone, 0, 2) . str_repeat('*', $length - 4) . substr($phone, -2);
    }
}

