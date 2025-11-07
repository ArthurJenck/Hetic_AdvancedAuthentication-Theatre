<?php

namespace App\Services\Auth;

use App\Entities\User;
use App\Services\TwoFactor\TwoFactorMethodInterface;
use App\Services\TwoFactor\TOTPMethod;
use App\Services\TwoFactor\EmailMethod;
use App\Services\TwoFactor\SMSMethod;
use App\Repositories\UserRepository;
use App\Repositories\TemporaryCodeRepository;
use App\Services\LogService;

class TwoFactorAuthenticationService
{
    private array $methods = [];
    private UserRepository $userRepository;
    private TemporaryCodeRepository $codeRepository;
    private LogService $logService;

    public function __construct(
        UserRepository $userRepository,
        TemporaryCodeRepository $codeRepository,
        LogService $logService
    ) {
        $this->userRepository = $userRepository;
        $this->codeRepository = $codeRepository;
        $this->logService = $logService;
        $this->initializeMethods();
    }

    private function initializeMethods(): void
    {
        $this->methods['totp'] = new TOTPMethod($this->userRepository);
        $this->methods['email'] = new EmailMethod($this->codeRepository);
        $this->methods['sms'] = new SMSMethod($this->codeRepository, $this->logService);
    }

    public function getMethod(string $methodName): ?TwoFactorMethodInterface
    {
        return $this->methods[$methodName] ?? null;
    }

    public function setupMethod(User $user, string $methodName, array $data = []): array
    {
        $method = $this->getMethod($methodName);
        
        if (!$method) {
            return [
                'success' => false,
                'message' => 'Méthode 2FA invalide',
                'data' => null
            ];
        }

        $result = $method->setup($user, $data);
        
        if ($result['success']) {
            $this->logService->log2FAAction($user->id, 'SETUP', $methodName, true);
        }
        
        return $result;
    }

    public function sendVerificationCode(User $user): array
    {
        if (!$user->twofa_enabled || $user->twofa_method === 'none') {
            return [
                'success' => false,
                'message' => '2FA non activée',
                'data' => null
            ];
        }

        if ($user->twofa_method !== 'totp') {
            $recentCodes = $this->codeRepository->countRecentCodes($user->id, $user->twofa_method, 10);
            
            if ($recentCodes >= 3) {
                return [
                    'success' => false,
                    'message' => 'Trop de tentatives. Veuillez réessayer dans 10 minutes.',
                    'data' => null
                ];
            }
        }

        $method = $this->getMethod($user->twofa_method);
        
        if (!$method) {
            return [
                'success' => false,
                'message' => 'Méthode 2FA non configurée',
                'data' => null
            ];
        }

        return $method->sendCode($user);
    }

    public function verifyCode(User $user, string $code): bool
    {
        if (!$user->twofa_enabled || $user->twofa_method === 'none') {
            return false;
        }

        $method = $this->getMethod($user->twofa_method);
        
        if (!$method) {
            return false;
        }

        $isValid = $method->verifyCode($user, $code);
        
        $this->logService->log2FAAction(
            $user->id,
            'VERIFY',
            $user->twofa_method,
            $isValid
        );
        
        return $isValid;
    }

    public function getAvailableMethods(): array
    {
        return [
            'totp' => [
                'name' => 'Application d\'authentification',
                'description' => 'Utilisez Google Authenticator, Microsoft Authenticator ou une application similaire',
                'icon' => '📱'
            ],
            'email' => [
                'name' => 'Code par email',
                'description' => 'Recevez un code de vérification par email',
                'icon' => '📧'
            ],
            'sms' => [
                'name' => 'Code par SMS',
                'description' => 'Recevez un code de vérification par SMS',
                'icon' => '💬'
            ]
        ];
    }
}

