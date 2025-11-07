<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion 2FA - Théâtre</title>
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

        <h1>Gestion de l'authentification à deux facteurs</h1>

        <?php if (isset($_GET['success'])): ?>
            <p class="success">
                <?php if ($_GET['success'] === 'enabled'): ?>
                    L'authentification à deux facteurs a été activée avec succès.
                <?php elseif ($_GET['success'] === 'disabled'): ?>
                    L'authentification à deux facteurs a été désactivée.
                <?php elseif ($_GET['success'] === 'changed'): ?>
                    Votre méthode 2FA a été changée avec succès.
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <div class="info-card">
            <h2>Statut actuel</h2>
            
            <?php if ($user->twofa_enabled && $user->twofa_method !== 'none'): ?>
                <div class="status-enabled">
                    <strong>✓ Activée</strong>
                    <p>Méthode : <?= htmlspecialchars($methods[$user->twofa_method]['name'] ?? $user->twofa_method) ?></p>
                </div>

                <div class="actions">
                    <form method="POST" action="<?= url('/profile/2fa/change-method') ?>" style="display: inline;">
                        <?= csrfField() ?>
                        <button type="submit" class="btn-primary">Changer de méthode</button>
                    </form>
                    
                    <form method="POST" action="<?= url('/profile/2fa/disable') ?>" 
                          onsubmit="return confirm('Êtes-vous sûr de vouloir désactiver la 2FA ?')"
                          style="display: inline;">
                        <?= csrfField() ?>
                        <button type="submit" class="btn-danger">Désactiver la 2FA</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="status-disabled">
                    <strong>✕ Désactivée</strong>
                    <p>Votre compte n'est pas protégé par l'authentification à deux facteurs.</p>
                </div>

                <form method="POST" action="<?= url('/profile/2fa/enable') ?>">
                    <?= csrfField() ?>
                    <button type="submit" class="btn-success">Activer la 2FA</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="info-section">
            <h2>Pourquoi activer la 2FA ?</h2>
            <ul>
                <li>Protège votre compte même si votre mot de passe est compromis</li>
                <li>Empêche les accès non autorisés</li>
                <li>Ajoute une couche de sécurité supplémentaire</li>
            </ul>
        </div>

        <a href="<?= url('/profile') ?>">&larr; Retour au profil</a>
    </div>
</body>

</html>

