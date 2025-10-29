<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil - Théâtre</title>
    <link rel="stylesheet" href="<?= url('/style.css') ?>">
</head>

<body>
    <div class="container">
        <nav>
            <a href="<?= url('/') ?>">Accueil</a>
            <a href="<?= url('/spectacles') ?>">Spectacles</a>
            <a href="<?= url('/profile') ?>">Mon profil</a>
            <?php if ($user->role === 'admin'): ?>
                <a href="<?= url('/spectacles/create') ?>">Créer un spectacle</a>
            <?php endif; ?>
            <a href="<?= url('/logout') ?>">Déconnexion</a>
        </nav>

        <h1>Mon profil</h1>

        <div class="user-info">
            <p><strong>Email :</strong> <?= htmlspecialchars($user->email) ?></p>
            <p><strong>Rôle :</strong> <?= htmlspecialchars($user->role) ?></p>
        </div>

        <h2>Mes réservations</h2>

        <?php if (empty($reservations)): ?>
            <p>Vous n'avez aucune réservation.</p>
        <?php else: ?>
            <?php foreach ($reservations as $reservation): ?>
                <div class="spectacle-card">
                    <h3><?= htmlspecialchars($reservation->spectacle_title) ?></h3>
                    <p><strong>Date :</strong> <?= htmlspecialchars(date('d/m/Y H:i', strtotime($reservation->spectacle_date))) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <a href="<?= url('/spectacles') ?>">Voir tous les spectacles</a>
    </div>
</body>

</html>