<?php

namespace App\Services\TwoFactor;

use App\Entities\User;
use App\Repositories\TemporaryCodeRepository;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class EmailMethod implements TwoFactorMethodInterface
{
    private TemporaryCodeRepository $codeRepository;
    private array $config;

    public function __construct(TemporaryCodeRepository $codeRepository)
    {
        $this->codeRepository = $codeRepository;
        $this->config = require __DIR__ . '/../../../config.php';
    }

    public function sendCode(User $user): array
    {
        $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', time() + 300);
        $this->codeRepository->create($user->id, $code, 'email', $expiresAt);

        try {
            $this->sendEmail($user->email, $code);

            return [
                'success' => true,
                'message' => 'Un code de vérification a été envoyé à votre adresse email',
                'data' => null
            ];
        } catch (PHPMailerException $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de l\'email: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function verifyCode(User $user, string $code): bool
    {
        return $this->codeRepository->verify($user->id, $code, 'email');
    }

    public function setup(User $user, array $data = []): array
    {
        return $this->sendCode($user);
    }

    public function getMethodName(): string
    {
        return 'email';
    }

    private function sendEmail(string $to, string $code): void
    {
        $mail = new PHPMailer(true);
        $emailConfig = $this->config['email'] ?? [];

        $mail->isSMTP();
        $mail->Host = $emailConfig['smtp_host'] ?? 'localhost';
        $mail->SMTPAuth = $emailConfig['smtp_auth'] ?? false;
        $mail->Username = $emailConfig['smtp_username'] ?? '';
        $mail->Password = $emailConfig['smtp_password'] ?? '';
        $mail->SMTPSecure = $emailConfig['smtp_secure'] ?? PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $emailConfig['smtp_port'] ?? 587;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom($emailConfig['from_email'] ?? 'noreply@arthurjenck.com', $emailConfig['from_name'] ?? 'Le Théâtre d\'Arthur Jenck');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = "Code de vérification - Théâtre d'Arthur Jenck";
        $mail->Body = $this->getEmailTemplate($code);
        $mail->AltBody = "Votre code de vérification est : $code\n\nCe code expire dans 5 minutes.";

        $mail->send();
    }

    private function getEmailTemplate(string $code): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .code { font-size: 32px; font-weight: bold; color: #007bff; text-align: center; 
                        padding: 20px; background: #f8f9fa; border-radius: 5px; margin: 20px 0; }
                .signature { margin-top: 30px; padding: 15px; background: #f8f9fa; border-left: 4px solid #007bff; 
                           font-size: 14px; color: #555; }
                .signature a { color: #007bff; text-decoration: none; font-weight: 500; }
                .signature a:hover { text-decoration: underline; }
                footer { font-size: 12px; color: #999; margin-top: 30px; padding-top: 20px; 
                         border-top: 1px solid #ddd; text-align: center; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Code de vérification</h2>
                <p>Votre code de vérification pour Theatre est :</p>
                <div class='code'>{$code}</div>
                <p>Ce code est valide pendant 5 minutes.</p>
                <div class='signature'>
                    <p>Si vous lisez ceci, n'hésitez pas à faire un tour sur mon 
                    <a href='https://www.linkedin.com/in/arthurjenck/'>LinkedIn</a> et mon 
                    <a href='https://arthurjenck.com'>portfolio</a>.</p>
                </div>
                <footer>
                    Si vous n'avez pas demandé ce code, ignorez cet email.
                </footer>
            </div>
        </body>
        </html>
        ";
    }
}
