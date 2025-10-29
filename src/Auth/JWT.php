<?php

namespace App\Auth;

class JWT
{
    private static ?self $instance = null;
    private string $secret;

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    private function __construct()
    {
        $config = require __DIR__ . '/../../config.php';
        $this->secret = $config['jwt']['secret'];
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function generateJWT(JWTPayload $payload): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];

        $headerBase64 = $this->base64UrlEncode(json_encode($header));
        $payloadBase64 = $this->base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', "$headerBase64.$payloadBase64", $this->secret, true);
        $signatureBase64 = $this->base64UrlEncode($signature);

        return "$headerBase64.$payloadBase64.$signatureBase64";
    }

    public function validateJWT(string $token): ?JWTPayload
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$headerBase64, $payloadBase64, $signatureBase64] = $parts;

        $expectedSignature = hash_hmac('sha256', "$headerBase64.$payloadBase64", $this->secret, true);
        $expectedSignatureBase64 = $this->base64UrlEncode($expectedSignature);

        if ($signatureBase64 !== $expectedSignatureBase64) {
            return null;
        }

        $data = json_decode($this->base64UrlDecode($payloadBase64), true);

        if (!$data || !isset($data['sub'], $data["exp"])) {
            return null;
        }

        $payload = new JWTPayload(
            $data['sub'],
            $data['email'] ?? '',
            $data['role'] ?? null
        );
        $payload->exp = $data['exp'];
        $payload->iat = $data['iat'];

        if ($payload->isExpired()) {
            return null;
        }

        return $payload;
    }

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
