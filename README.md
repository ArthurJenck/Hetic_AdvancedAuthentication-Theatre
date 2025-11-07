# Hetic_AdvancedAuthentication-Theatre

## TP d'Authentification Avancée - Hetic WEB3 2026

### — Arthur JENCK

## Installation

### Prérequis

- PHP 8.1 ou supérieur
- XAMPP avec phpMyAdmin
- Composer

### Étapes d'installation

1. Créer la base de données `aa_theatre` dans phpMyAdmin
2. Copier-coller et exécuter le script SQL ci-dessous dans l'onglet SQL de phpMyAdmin
3. Installer les dépendances avec la commande :

   ```bash
   composer install
   ```

4. Le fichier `config.php` est déjà configuré avec les informations nécessaires
5. Accéder au site via : [http://localhost/AuthentificationAvancee/theatre/public](http://localhost/AuthentificationAvancee/theatre/public)

### Script SQL

```sql
DROP DATABASE IF EXISTS aa_theatre;
CREATE DATABASE aa_theatre CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE aa_theatre;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user',
    twofa_secret VARCHAR(255) NULL,
    twofa_method ENUM('none', 'totp', 'email', 'sms') DEFAULT 'none',
    twofa_enabled BOOLEAN DEFAULT FALSE,
    phone_number VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE spectacles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    date DATETIME NOT NULL,
    available_seats INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    spectacle_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (spectacle_id) REFERENCES spectacles(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_spectacle (spectacle_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE temporary_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code VARCHAR(6) NOT NULL,
    method VARCHAR(10) NOT NULL,
    expires_at DATETIME NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_method (user_id, method),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (email, password, role, twofa_method, twofa_enabled) VALUES
('alicedupont@gmail.com', '$2y$10$xTkTQbzgpyltfgVZU5ABOefeVdFDgbWK/eTj001U9ZneaVg/pqFW2', 'admin', 'none', FALSE);

INSERT INTO spectacles (title, description, date, available_seats) VALUES
('Le Malade imaginaire', 'Comédie-ballet de Molière mettant en scène Argan, un hypocondriaque obsédé par sa santé.', '2025-12-15 20:00:00', 150),
('Roméo et Juliette', 'Tragédie romantique de Shakespeare racontant l\'histoire d\'amour impossible entre deux jeunes amants.', '2025-12-20 19:30:00', 200),
('Le Cid', 'Tragi-comédie de Corneille sur le conflit entre l\'amour et l\'honneur.', '2026-01-10 20:30:00', 180),
('Les Misérables', 'Adaptation théâtrale du roman de Victor Hugo sur la quête de rédemption de Jean Valjean.', '2026-01-25 19:00:00', 250);
```

### Compte de test

- **Email** : <alicedupont@gmail.com>
- **Mot de passe** : secret123
- **Rôle** : Administrateur

## Enoncé

On veut un site de spectacles
Les cas d'utilisation sont les suivants :

- Page d'accueil -> publique
  -> Menu de navigation
  -> Message de bienvnenue
  -> Si un utilisateur est identifué, afficher son nom
- Page liste les spectacles -> publique
- Page fiche spectacles -> publique
- Réserver une place -> utilisateurs identifiés (inscrit sur le site)
- Accéder à une page de profil : liste des billets que j'ai réservés -> utilisateurs identifiés
- Ajouter des spectacles -> administrateurs du site  
*Données : un SGBD n'est pas obligatoire*

### Contraintes

- Routeurs
- Contrôleurs -> méthodes qui résolvent chaque cas d'utilisation
-> En option : Implémenter le middleware sous forme d'attribut PHP

[# Attribute]
function IsGranted()

[# IsGranted ]
function f()

- Filtre les accès en fonction des contrainte particulières des cas d'utilisations
- Faire une application PHP "normalisée"
  -> Classes
  -> Des espace de nom (spacename)

### Authentification 2FA

L'utilisateur doit pouvoir s'identifier et rester reconnu par l'application avec des jetons JWT

1. L'utilisateur s'identifie avec son mot de passe
2. On demande à l'utilisateur de confirmer son identité  
Il peut choisir entre :
    - recevoir un mail
    - recevoir un SMS
    - s'identifier avec un QR code et enregistrer la référence au site dans une application TOTP
        - Google Authenticator
        - TOTP Authenticator
        - Microsoft Authenticator
3. Autoriser l'utilisateur à accéder à certaines ressources

- Faire en sorte de laisser le choix à l'utilisateur d'avoir des formes de validation différentes pour l'A2F, voir moyens ci-dessus
- Stocker méthode choisie dans base de données
