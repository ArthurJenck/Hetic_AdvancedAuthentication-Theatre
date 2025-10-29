<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($spectacle->title) ?> - Théâtre</title>
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

        <h1><?= htmlspecialchars($spectacle->title) ?></h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="success">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if ($spectacle->description): ?>
            <p><?= htmlspecialchars($spectacle->description) ?></p>
        <?php endif; ?>

        <p><strong>Date :</strong> <?= htmlspecialchars(date('d/m/Y H:i', strtotime($spectacle->date))) ?></p>
        <p><strong>Places disponibles :</strong> <?= $spectacle->available_seats ?></p>

        <?php if ($user && $spectacle->hasAvailableSeats()): ?>
            <form method="POST" action="<?= url('/reservations') ?>">
                <input type="hidden" name="spectacle_id" value="<?= $spectacle->id ?>">
                <button type="submit">Réserver une place</button>
            </form>
        <?php elseif (!$user): ?>
            <p>Vous devez être connecté pour réserver.</p>
            <a href="<?= url('/login') ?>">Se connecter</a>
        <?php else: ?>
            <p>Plus de places disponibles.</p>
        <?php endif; ?>

        <a href="<?= url('/spectacles') ?>">Retour à la liste</a>
    </div>
</body>

</html>