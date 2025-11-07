<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration 2FA - Théâtre</title>
    <link rel="stylesheet" href="<?= url('/style.css') ?>">
</head>

<body>
    <div class="container">
        <h1>Configuration de l'authentification à deux facteurs</h1>

        <p>Choisissez votre méthode de vérification préférée :</p>

        <?php if (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <div class="method-selection">
            <?php foreach ($methods as $key => $method): ?>
                <form method="POST" action="<?= url('/setup-2fa-choice') ?>" style="margin: 0;">
                    <?= csrfField() ?>
                    <input type="hidden" name="method" value="<?= $key ?>">
                    <button type="submit" class="method-card">
                        <div class="method-content">
                            <div class="method-icon"><?= $method['icon'] ?></div>
                            <h3><?= htmlspecialchars($method['name']) ?></h3>
                            <p><?= htmlspecialchars($method['description']) ?></p>
                        </div>
                    </button>
                </form>
            <?php endforeach; ?>
        </div>

        <?php if ($canSkip): ?>
            <form method="POST" action="<?= url('/setup-2fa-choice') ?>" style="text-align: center; margin-top: 20px;">
                <?= csrfField() ?>
                <button type="submit" name="method" value="skip" class="btn-secondary">Configurer plus tard</button>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>

