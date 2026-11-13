# Sama Plaintes

Sama Plaintes est une solution numérique permettant aux citoyens ou utilisateurs de signaler des problèmes, déposer des plaintes et suivre leur traitement depuis une plateforme centralisée.

L’objectif est de rendre la gestion des réclamations plus simple, transparente et traçable.

## Fonctionnalités principales

- Dépôt d’une plainte ou réclamation
- Ajout de photos et de documents comme preuves
- Localisation du problème
- Catégorisation des plaintes
- Génération d’un numéro de suivi
- Suivi de l’état de la plainte
- Notifications concernant l’évolution du dossier
- Espace utilisateur
- Interface de gestion pour les responsables
- Tableau de bord avec statistiques
- Gestion des utilisateurs et des permissions

## Exemple

Un citoyen constate un problème dans son quartier : éclairage public défectueux, voirie endommagée, problème d’assainissement. Il peut prendre une photo, décrire le problème, indiquer sa localisation et envoyer la plainte. Les responsables reçoivent le dossier, le traitent et mettent à jour son état.

## Démarrage

Le projet utilise Laravel, PHP et PostgreSQL.

```bash
composer install
npm install
npm run build
php artisan migrate --seed
php artisan serve
```

Comptes de démonstration, mot de passe `Sama2025` :

- Citoyen : `issa.bathily@samaplaintes.sn`
- Autorité : `mairie.diamniadio@samaplaintes.sn`
- Superviseur : `serigne.diagne@samaplaintes.sn`
