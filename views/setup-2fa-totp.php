<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration TOTP - Théâtre</title>
    <link rel="stylesheet" href="<?= url('/style.css') ?>">
</head>

<body>
    <div class="container">
        <h1>Configuration de l'authentification TOTP</h1>

        <p>Scannez ce QR code avec votre application d'authentification :</p>

        <div style="text-align: center; margin: 20px 0;">
            <img src="<?= $qrCodeDataUri ?>" alt="QR Code 2FA" style="max-width: 300px;" />
        </div>

        <p><strong>Applications recommandées :</strong></p>
        <ul>
            <li>Google Authenticator</li>
            <li>Microsoft Authenticator</li>
            <li>Authy</li>
        </ul>

        <?php if (isset($error)): ?>
            <p class="error">
                <?php if ($error === 'invalid_code'): ?>
                    Code incorrect. Veuillez réessayer.
                <?php elseif ($error === 'missing_code'): ?>
                    Veuillez entrer un code.
                <?php else: ?>
                    <?= htmlspecialchars($error) ?>
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="<?= url('/setup-2fa/complete') ?>">
            <?= csrfField() ?>
            
            <label for="code">Entrez le code à 6 chiffres pour vérifier :</label>
            <input
                type="text"
                id="code"
                name="code"
                required
                pattern="[0-9]{6}"
                maxlength="6"
                placeholder="000000"
                autofocus>

            <button type="submit">Valider</button>
        </form>
    </div>
</body>

</html>

