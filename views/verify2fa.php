<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isSetup ? 'Configuration 2FA' : 'Vérification 2FA' ?></title>
    <link rel="stylesheet" href="<?= url('/style.css') ?>">
</head>

<body>
    <div class="container">
        <h1><?= $isSetup ? 'Configuration de l\'authentification à deux facteurs' : 'Authentification à deux facteurs' ?></h1>

        <?php if ($isSetup): ?>
            <p>Scannez ce QR code avec votre application d'authentification :</p>

            <div style="text-align: center; margin: 20px 0;">
                <img src="<?= $qrCodeDataUri ?>" alt="QR Code 2FA" />
            </div>

            <p>Applications recommandées : Google Authenticator, Microsoft Authenticator, Authy</p>
        <?php else: ?>
            <p>Entrez le code à 6 chiffres depuis votre application d'authentification</p>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <p style="color: red;">
                <?php if ($error === 'invalid_code'): ?>
                    Code incorrect. Veuillez réessayer.
                <?php elseif ($error === 'missing_code'): ?>
                    Veuillez entrer un code.
                <?php else: ?>
                    <?= htmlspecialchars($error) ?>
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="<?= $formAction ?>">
            <div>
                <label for="code"><?= $isSetup ? 'Entrez le code à 6 chiffres pour vérifier :' : 'Code 2FA :' ?></label>
                <input
                    type="text"
                    id="code"
                    name="code"
                    required
                    pattern="[0-9]{6}"
                    maxlength="6"
                    placeholder="000000"
                    autofocus>
            </div>
            <button type="submit"><?= $isSetup ? 'Valider' : 'Vérifier' ?></button>
        </form>

        <?php if (!$isSetup): ?>
            <p><a href="<?= url('/login') ?>">Retour à la connexion</a></p>
        <?php endif; ?>
    </div>
</body>

</html>