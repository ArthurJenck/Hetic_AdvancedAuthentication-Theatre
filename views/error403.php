<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès refusé - Théâtre</title>
    <link rel="stylesheet" href="<?= url('/style.css') ?>">
</head>

<body>
    <div class="container">
        <nav>
            <a href="<?= url('/') ?>">Accueil</a>
            <a href="<?= url('/spectacles') ?>">Spectacles</a>
        </nav>

        <h1>Accès refusé</h1>

        <div class="error">
            <p>Vous n'avez pas les permissions nécessaires pour accéder à cette page.</p>
        </div>

        <a href="<?= url('/') ?>">Retour à l'accueil</a>
    </div>
</body>

</html>