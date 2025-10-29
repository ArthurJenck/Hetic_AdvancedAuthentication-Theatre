<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spectacles - Théâtre</title>
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

        <h1>Spectacles</h1>

        <?php if (empty($spectacles)): ?>
            <p>Aucun spectacle disponible pour le moment.</p>
        <?php else: ?>
            <?php foreach ($spectacles as $spectacle): ?>
                <div class="spectacle-card">
                    <h3><?= htmlspecialchars($spectacle->title) ?></h3>
                    <?php if ($spectacle->description): ?>
                        <p><?= htmlspecialchars($spectacle->description) ?></p>
                    <?php endif; ?>
                    <p><strong>Date :</strong> <?= htmlspecialchars(date('d/m/Y H:i', strtotime($spectacle->date))) ?></p>
                    <p><strong>Places disponibles :</strong> <?= $spectacle->available_seats ?></p>
                    <a href="<?= url('/spectacles/' . $spectacle->id) ?>">Voir les détails</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>

</html>