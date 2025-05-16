# TacTâche - Application de Gestion de Tâches

TacTâche est une application web de gestion de tâches simple et efficace, permettant aux équipes de collaborer sur des projets en organisant leurs tâches dans un tableau Kanban et un calendrier. Conçue pour améliorer la productivité et la collaboration, TacTâche offre une interface intuitive et des fonctionnalités adaptées aux besoins des équipes modernes.

## Fonctionnalités

- **Tableau des Statuts** : Visualisez vos tâches organisées par statut (À Faire, En Cours, Terminé) dans une interface de type Kanban claire et intuitive
- **Vue Calendrier** : Planifiez vos tâches dans le temps avec une interface de calendrier interactive permettant de visualiser les échéances
- **Gestion des utilisateurs** : Système d'authentification complet avec deux rôles distincts (Manager et Collaborateur)
- **Permissions différenciées** :
  - Les managers peuvent créer, modifier et supprimer des tâches pour tous les membres de l'équipe
  - Les collaborateurs peuvent gérer uniquement leurs propres tâches (créées ou assignées)

## Architecture et Structure du Projet

TacTâche suit une architecture MVC (Modèle-Vue-Contrôleur) simplifiée, organisée de manière claire et modulaire pour faciliter la maintenance et l'évolution du code :

### Organisation des fichiers

```
TacTâche/
├── database/              # Scripts SQL pour la base de données
│   └── init.sql           # Script d'initialisation de la base de données
├── public/                # Fichiers accessibles publiquement (Vues)
│   ├── assets/            # Ressources statiques (CSS, JS, images)
│   │   ├── css/           # Feuilles de style CSS
│   │   ├── js/            # Scripts JavaScript
│   │   └── img/           # Images
│   ├── index.php          # Page principale (tableau Kanban)
│   ├── calendar.php       # Vue calendrier
│   ├── login.php          # Page de connexion
│   ├── register.php       # Page d'inscription
│   ├── logout.php         # Déconnexion
│   └── task.php           # Détails d'une tâche
└── src/                   # Code source PHP
    ├── actions/           # Contrôleurs pour les actions AJAX
    │   ├── task_api.php   # API centralisée pour les opérations sur les tâches
    │   ├── get_users.php  # Récupération des utilisateurs
    │   ├── login_action.php # Traitement de la connexion
    │   └── register_action.php # Traitement de l'inscription
    ├── classes/           # Classes PHP (Modèles)
    │   ├── Database.php   # Gestion de la connexion à la base de données
    │   ├── Task.php       # Gestion des tâches
    │   ├── User.php       # Gestion des utilisateurs
    │   └── Utility.php    # Fonctions utilitaires
    └── includes/          # Fichiers inclus (configuration, utilitaires)
        ├── config.php     # Configuration de l'application
        └── helpers.php    # Fonctions d'aide
```

### Principes d'architecture

- **Modèles** (Model) :
  - Classes PHP dans `src/classes/` qui encapsulent la logique métier et l'accès aux données
  - Chaque classe représente une entité du domaine (Task, User) avec ses opérations associées
  - Séparation claire des responsabilités entre les différentes classes

- **Vues** (View) :
  - Fichiers PHP dans `public/` qui gèrent l'affichage et l'interface utilisateur
  - Utilisation de HTML, CSS et JavaScript pour créer une interface interactive
  - Séparation du contenu (HTML), de la présentation (CSS) et du comportement (JavaScript)

- **Contrôleurs** (Controller) :
  - Scripts dans `src/actions/` qui traitent les requêtes HTTP et les actions utilisateur
  - API centralisée (`task_api.php`) pour toutes les opérations CRUD sur les tâches
  - Validation des données et gestion des permissions avant traitement

Cette architecture permet une séparation claire des responsabilités, facilitant la maintenance et l'évolution de l'application.

## Rôles et Permissions

L'application TacTâche implémente un système de contrôle d'accès basé sur les rôles (RBAC) avec deux profils utilisateur distincts :

### Manager
- **Création de tâches** : Peut créer des tâches et les assigner à n'importe quel utilisateur
- **Gestion globale** : Peut modifier et supprimer toutes les tâches, quel que soit leur créateur
- **Assignation** : Peut réassigner une tâche à un autre utilisateur à tout moment
- **Supervision** : A accès à une vue d'ensemble de toutes les tâches et de leur progression
- **Administration** : A accès à toutes les fonctionnalités de l'application

### Collaborateur
- **Création limitée** : Peut créer des tâches mais ne peut les assigner qu'à lui-même
- **Gestion personnelle** : Peut modifier et supprimer uniquement les tâches qu'il a créées ou qui lui sont assignées
- **Visibilité** : Peut voir les tâches des autres utilisateurs mais ne peut pas les modifier
- **Statut** : Peut changer le statut de ses propres tâches (À Faire, En Cours, Terminé)

### Règles communes
- Tous les utilisateurs peuvent consulter le calendrier des tâches
- Tous les utilisateurs peuvent filtrer et rechercher des tâches

## Développement

### Technologies utilisées

TacTâche utilise les technologies suivantes :

- **Backend** :
  - **PHP** : Programmation orientée objet
  - **PDO** : Requêtes préparées pour l'accès à la base de données
  - **Sessions PHP** : Gestion de l'authentification utilisateur

- **Base de données** :
  - **MySQL** : Stockage des données
  - **Clés étrangères** : Relations entre utilisateurs et tâches

- **Frontend** :
  - **HTML/CSS** : Interface utilisateur
  - **JavaScript** : Interactivité côté client
  - **Responsive Design** : Adaptation à différentes tailles d'écran

- **Bibliothèques** :
  - **FullCalendar** : Affichage des tâches dans un calendrier
  - **Axios** : Requêtes AJAX pour les opérations sans rechargement de page
  - **Font Awesome** : Icônes pour l'interface

### Fonctionnalités de sécurité

- **Authentification** : Système de connexion sécurisé
- **Hachage des mots de passe** : Utilisation de password_hash()
- **Validation des données** : Vérification des entrées utilisateur
- **Contrôle d'accès** : Permissions basées sur les rôles utilisateur

