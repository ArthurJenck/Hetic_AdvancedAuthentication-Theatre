<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration Email - Théâtre</title>
    <link rel="stylesheet" href="<?= url('/style.css') ?>">
</head>

<body>
    <div class="container">
        <h1>Configuration de l'authentification par email</h1>

        <?php if (isset($message)): ?>
            <p class="success"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <p>Un code de vérification a été envoyé à votre adresse email.</p>
        <p>Consultez votre boîte de réception et entrez le code ci-dessous :</p>

        <?php if (isset($error)): ?>
            <p class="error">
                <?php if ($error === 'invalid_code'): ?>
                    Code incorrect. Vérifiez votre email.
                <?php elseif ($error === 'missing_code'): ?>
                    Veuillez entrer un code.
                <?php else: ?>
                    <?= htmlspecialchars($error) ?>
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="<?= url('/setup-2fa/complete') ?>">
            <?= csrfField() ?>
            
            <label for="code">Code de vérification :</label>
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

