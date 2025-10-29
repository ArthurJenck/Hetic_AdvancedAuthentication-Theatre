<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Théâtre</title>
    <link rel="stylesheet" href="<?= url('/style.css') ?>">
</head>

<body>
    <div class="container">
        <nav>
            <a href="<?= url('/') ?>">Accueil</a>
            <a href="<?= url('/spectacles') ?>">Spectacles</a>
            <?php if ($user): ?>
                <a href="<?= url('/profile') ?>">Mon profil</a>
                <?php if ($user->role === 'admin'): ?>
                    <a href="<?= url('/spectacles/create') ?>">Créer un spectacle</a>
                <?php endif; ?>
                <a href="<?= url('/logout') ?>">Déconnexion</a>
            <?php else: ?>
                <a href="<?= url('/login') ?>">Connexion</a>
            <?php endif; ?>
        </nav>

        <h1>Bienvenue</h1>

        <?php if ($user): ?>
            <div class="user-info">
                <p>Connecté en tant que : <strong><?= htmlspecialchars($user->email) ?></strong></p>
                <p>Rôle : <strong><?= htmlspecialchars($user->role) ?></strong></p>
            </div>
        <?php endif; ?>

        <p>Réservez vos places pour les spectacles à venir.</p>

        <a href="<?= url('/spectacles') ?>">Voir les spectacles</a>
    </div>
</body>

</html>