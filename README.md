# Application de Gestion de Tâches (To-Do App)

Une application web simple et intuitive pour la gestion de tâches, conçue pour les équipes avec différents niveaux d'accès.

## Fonctionnalités

- **Gestion des tâches** : Créer, modifier, supprimer et visualiser des tâches
- **Tableau Kanban** : Visualiser les tâches par statut (À faire, En cours, Terminé)
- **Vue Calendrier** : Visualiser les tâches sur un calendrier
- **Système d'authentification** : Inscription et connexion des utilisateurs
- **Contrôle d'accès basé sur les rôles** : Managers et Collaborateurs avec différentes permissions

## Structure du Projet

```
To-Do-App/
├── database/
│   └── init.sql                # Script d'initialisation de la base de données
├── public/
│   ├── assets/
│   │   ├── css/                # Fichiers CSS
│   │   └── js/                 # Fichiers JavaScript
│   ├── calendar.php            # Page de calendrier
│   ├── index.php               # Page principale (tableau Kanban)
│   ├── login.php               # Page de connexion
│   ├── logout.php              # Script de déconnexion
│   ├── register.php            # Page d'inscription
│   └── task.php                # Page de détail d'une tâche
└── src/
    ├── actions/                # Scripts d'action (API)
    ├── classes/                # Classes PHP
    └── includes/               # Fichiers inclus (configuration, etc.)
```

## Technologies Utilisées

- **Backend** : PHP 7.4+
- **Base de données** : MySQL
- **Frontend** : HTML, CSS, JavaScript
- **Bibliothèques** : Axios (AJAX), FullCalendar (calendrier)

## Installation

1. Clonez ce dépôt dans votre répertoire web (par exemple, `htdocs` pour XAMPP)
2. Créez une base de données MySQL nommée `task_manager`
3. Importez le fichier `database/init.sql` pour créer les tables nécessaires
4. Configurez les paramètres de connexion à la base de données dans `src/includes/config.php`
5. Accédez à l'application via votre navigateur (par exemple, `http://localhost/To-Do-App/public/`)

## Utilisation

### Inscription et Connexion

1. Accédez à la page d'inscription pour créer un compte
2. Connectez-vous avec vos identifiants

### Gestion des Tâches

- **Créer une tâche** : Cliquez sur "Nouvelle tâche" et remplissez le formulaire
- **Modifier une tâche** : Cliquez sur l'icône de modification d'une tâche
- **Supprimer une tâche** : Cliquez sur l'icône de suppression d'une tâche
- **Changer le statut** : Modifiez une tâche et changez son statut

### Vue Calendrier

- Accédez à la page Calendrier pour visualiser les tâches par date
- Cliquez sur une tâche dans le calendrier pour voir ses détails

## Rôles et Permissions

### Manager

- Accès complet à toutes les tâches
- Peut créer, modifier et supprimer n'importe quelle tâche
- Peut assigner des tâches à n'importe quel utilisateur

### Collaborateur

- Peut créer des tâches pour lui-même
- Peut modifier les tâches qu'il a créées ou qui lui sont assignées
- Peut voir toutes les tâches dans la vue calendrier

## Structure des Classes

### User

Gère l'authentification et les permissions des utilisateurs.

### Task

Gère les opérations CRUD (Création, Lecture, Mise à jour, Suppression) des tâches.

### Database

Gère la connexion à la base de données.

### Utility

Fournit des fonctions utilitaires pour l'application.

## Développement

Cette application est conçue pour être simple et facile à comprendre, avec une structure orientée objet. Elle est idéale pour les projets d'apprentissage et peut être étendue avec des fonctionnalités supplémentaires.

## Licence

Ce projet est disponible sous licence MIT. Voir le fichier LICENSE pour plus de détails.
