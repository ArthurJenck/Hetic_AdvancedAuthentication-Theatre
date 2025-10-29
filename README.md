# Hetic_AdvancedAuthentication-Theatre

## TP d'Authentification Avancée - Hetic WEB3 2026

### — Arthur JENCK

### Enoncé

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

#### Contraintes

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
