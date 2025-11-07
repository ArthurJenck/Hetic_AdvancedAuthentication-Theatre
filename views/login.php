<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Théâtre</title>
    <link rel="stylesheet" href="<?= url('/style.css') ?>">
</head>

<body>
    <div class="container">
        <nav>
            <a href="<?= url('/') ?>">Accueil</a>
            <a href="<?= url('/spectacles') ?>">Spectacles</a>
        </nav>

        <h1>Connexion</h1>

        <form method="POST" action="<?= url('/login') ?>">
            <?= csrfField() ?>
            
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required placeholder="alicedupont@gmail.com">

            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required placeholder="secret123">

            <button type="submit">Se connecter</button>
        </form>

        <p style="text-align: center; margin-top: 20px;">
            Pas encore de compte ? <a href="<?= url('/register') ?>">Inscrivez-vous</a>
        </p>
    </div>
</body>

</html>