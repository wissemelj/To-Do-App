# Application de Gestion de Tâches (To-Do App)

Une application simple de gestion de tâches avec authentification et contrôle d'accès basé sur les rôles.

## Structure du Projet

```
To-Do-App/
│
├── database/                  # Scripts SQL pour la base de données
│
├── public/                    # Fichiers accessibles publiquement
│   ├── assets/                # Ressources statiques
│   │   ├── css/               # Fichiers CSS
│   │   ├── js/                # Fichiers JavaScript
│   │   └── img/               # Images
│   │
│   ├── index.php              # Page principale (tableau de bord)
│   ├── calendar.php           # Vue calendrier
│   ├── login.php              # Page de connexion
│   ├── register.php           # Page d'inscription
│   └── logout.php             # Script de déconnexion
│
└── src/                       # Code source PHP
    ├── actions/               # Scripts de traitement des actions
    │   ├── login_action.php   # Traitement de la connexion
    │   ├── register_action.php # Traitement de l'inscription
    │   ├── task_action.php    # Création de tâche
    │   ├── edit_task.php      # Modification de tâche
    │   ├── delete_task.php    # Suppression de tâche
    │   ├── get_task.php       # Récupération des détails d'une tâche
    │   ├── get_users.php      # Récupération de la liste des utilisateurs
    │   └── ...
    │
    └── includes/              # Fichiers inclus dans plusieurs pages
        ├── auth.php           # Fonctions d'authentification
        ├── config.php         # Configuration de l'application
        ├── database.php       # Connexion à la base de données
        └── utils.php          # Fonctions utilitaires
```

## Fonctionnalités

- **Authentification** : Connexion, inscription et déconnexion
- **Gestion des tâches** : Création, modification et suppression de tâches
- **Contrôle d'accès basé sur les rôles** :
  - **Managers** : Accès complet à toutes les tâches
  - **Collaborateurs** : Peuvent modifier les tâches qu'ils ont créées ou qui leur sont assignées
- **Vue Tableau de bord** : Affichage des tâches par statut (À faire, En cours, Terminé)
- **Vue Calendrier** : Affichage des tâches dans un calendrier

## Technologies Utilisées

- **Frontend** : HTML, CSS, JavaScript, Axios (pour les requêtes AJAX)
- **Backend** : PHP
- **Base de données** : MySQL
- **Bibliothèques** : FullCalendar (pour la vue calendrier)

## Installation

1. Clonez ce dépôt dans le répertoire `htdocs` de XAMPP
2. Importez le fichier SQL dans la base de données MySQL
3. Configurez les paramètres de connexion à la base de données dans `src/includes/config.php`
4. Accédez à l'application via `http://localhost/To-Do-App/public/`

## Utilisateurs par défaut

- **Manager** :
  - Nom d'utilisateur : `admin`
  - Mot de passe : `admin123`
- **Collaborateur** :
  - Nom d'utilisateur : `user`
  - Mot de passe : `user123`
