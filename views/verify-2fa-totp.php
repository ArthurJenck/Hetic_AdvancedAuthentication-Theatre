<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification 2FA - Théâtre</title>
    <link rel="stylesheet" href="<?= url('/style.css') ?>">
</head>

<body>
    <div class="container">
        <h1>Authentification à deux facteurs</h1>

        <p>Entrez le code à 6 chiffres depuis votre application d'authentification</p>

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

        <form method="POST" action="<?= url('/verify-2fa') ?>">
            <?= csrfField() ?>
            
            <label for="code">Code 2FA :</label>
            <input
                type="text"
                id="code"
                name="code"
                required
                pattern="[0-9]{6}"
                maxlength="6"
                placeholder="000000"
                autofocus>

            <button type="submit">Vérifier</button>
        </form>

        <p><a href="<?= url('/login') ?>">Retour à la connexion</a></p>
    </div>
</body>

</html>

