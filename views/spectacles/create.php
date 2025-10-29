<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un spectacle - Théâtre</title>
    <link rel="stylesheet" href="<?= url('/style.css') ?>">
</head>

<body>
    <div class="container">
        <nav>
            <a href="<?= url('/') ?>">Accueil</a>
            <a href="<?= url('/spectacles') ?>">Spectacles</a>
            <a href="<?= url('/profile') ?>">Mon profil</a>
            <a href="<?= url('/logout') ?>">Déconnexion</a>
        </nav>

        <h1>Créer un spectacle</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= url('/spectacles/create') ?>">
            <label for="title">Titre</label>
            <input type="text" id="title" name="title" required>

            <label for="description">Description</label>
            <textarea id="description" name="description"></textarea>

            <label for="date">Date et heure</label>
            <input type="datetime-local" id="date" name="date" required>

            <label for="available_seats">Nombre de places</label>
            <input type="number" id="available_seats" name="available_seats" min="1" required>

            <button type="submit">Créer le spectacle</button>
        </form>

        <a href="<?= url('/spectacles') ?>">Retour à la liste</a>
    </div>
</body>

</html>