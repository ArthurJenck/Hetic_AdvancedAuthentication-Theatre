<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration SMS - Théâtre</title>
    <link rel="stylesheet" href="<?= url('/style.css') ?>">
</head>

<body>
    <div class="container">
        <h1>Configuration de l'authentification par SMS</h1>

        <p>Entrez votre numéro de téléphone pour recevoir les codes de vérification :</p>

        <?php if (isset($error)): ?>
            <p class="error">
                <?php if ($error === 'missing_phone'): ?>
                    Veuillez entrer un numéro de téléphone.
                <?php else: ?>
                    <?= htmlspecialchars($error) ?>
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="<?= url('/setup-2fa/sms/phone') ?>">
            <?= csrfField() ?>

            <label for="phone_number">Numéro de téléphone :</label>
            <input
                type="tel"
                id="phone_number"
                name="phone_number"
                required
                placeholder="+33612345678"
                autofocus>

            <p class="info">Format : +33612345678 (avec l'indicatif pays)</p>

            <button type="submit">Envoyer un code de vérification</button>
        </form>
    </div>
</body>

</html>