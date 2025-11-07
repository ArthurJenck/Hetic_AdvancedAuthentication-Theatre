<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Théâtre</title>
    <link rel="stylesheet" href="<?= url('/style.css') ?>">
</head>

<body>
    <div class="container">
        <nav>
            <a href="<?= url('/') ?>">Accueil</a>
            <a href="<?= url('/spectacles') ?>">Spectacles</a>
            <a href="<?= url('/login') ?>">Connexion</a>
        </nav>

        <h1>Inscription</h1>

        <?php if (isset($error)): ?>
            <p class="error">
                <?php if ($error === 'email_exists'): ?>
                    Cet email est déjà utilisé.
                <?php elseif ($error === 'passwords_mismatch'): ?>
                    Les mots de passe ne correspondent pas.
                <?php elseif ($error === 'invalid_email'): ?>
                    Adresse email invalide.
                <?php elseif ($error === 'weak_password'): ?>
                    Le mot de passe doit contenir au moins 6 caractères.
                <?php else: ?>
                    <?= htmlspecialchars($error) ?>
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="<?= url('/register') ?>" onsubmit="return validateForm()">
            <?= csrfField() ?>
            
            <label for="email">Email</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                required 
                placeholder="votre@email.com"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

            <label for="password">Mot de passe</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                required 
                minlength="6"
                placeholder="Au moins 6 caractères">

            <label for="confirm_password">Confirmer le mot de passe</label>
            <input 
                type="password" 
                id="confirm_password" 
                name="confirm_password" 
                required 
                minlength="6"
                placeholder="Retapez votre mot de passe">

            <button type="submit">S'inscrire</button>
        </form>

        <p style="text-align: center; margin-top: 20px;">
            Déjà un compte ? <a href="<?= url('/login') ?>">Connectez-vous</a>
        </p>
    </div>

    <script>
        function validateForm() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                alert('Les mots de passe ne correspondent pas.');
                return false;
            }

            if (password.length < 6) {
                alert('Le mot de passe doit contenir au moins 6 caractères.');
                return false;
            }

            return true;
        }
    </script>

    <style>
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            border: 1px solid #f5c6cb;
        }

        form {
            max-width: 500px;
            margin: 30px auto;
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        button {
            width: 100%;
            margin-top: 20px;
            padding: 12px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }
    </style>
</body>

</html>

