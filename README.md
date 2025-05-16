# TacTâche - Application de Gestion de Tâches

TacTâche est une application web de gestion de tâches simple et efficace, permettant aux équipes de collaborer sur des projets en organisant leurs tâches dans un tableau Kanban et un calendrier. Conçue pour améliorer la productivité et la collaboration, TacTâche offre une interface intuitive et des fonctionnalités adaptées aux besoins des équipes modernes.

## Fonctionnalités principales

- **Tableau Kanban (Backlog)** : Visualisez vos tâches organisées par statut (À Faire, En Cours, Terminé) dans une interface claire et intuitive
- **Vue Calendrier** : Planifiez vos tâches dans le temps avec une interface de calendrier interactive
- **Exportation PDF** : Exportez vos tâches au format PDF avec un design moderne et compact
- **Gestion des utilisateurs** : Système d'authentification avec deux rôles (Manager et Collaborateur)
- **Ajout de photos** : Possibilité d'ajouter des images aux tâches pour une meilleure documentation

## Architecture et Structure

TacTâche suit une architecture MVC (Modèle-Vue-Contrôleur) simplifiée, organisée de manière modulaire pour faciliter la maintenance et l'évolution du code.

### Structure du projet

```
TacTâche/
├── database/              # Scripts SQL pour la base de données
│   └── init.sql           # Script d'initialisation de la base de données
├── lib/                   # Bibliothèques externes
│   └── tcpdf/             # Bibliothèque pour la génération de PDF
├── public/                # Fichiers accessibles publiquement (Vues)
│   ├── assets/            # Ressources statiques
│   │   ├── css/           # Feuilles de style CSS
│   │   └── js/            # Scripts JavaScript
│   ├── uploads/           # Fichiers téléchargés (photos des tâches)
│   ├── index.php          # Page principale (tableau Kanban)
│   ├── calendar.php       # Vue calendrier
│   ├── login.php          # Page de connexion
│   ├── register.php       # Page d'inscription
│   ├── logout.php         # Déconnexion
│   └── task.php           # Détails d'une tâche
└── src/                   # Code source PHP
    ├── actions/           # Contrôleurs pour les actions AJAX
    │   ├── export_tasks.php # Exportation des tâches en PDF
    │   ├── get_users.php    # Récupération des utilisateurs
    │   ├── login_action.php # Traitement de la connexion
    │   ├── register_action.php # Traitement de l'inscription
    │   └── task_api.php     # API pour les opérations sur les tâches
    ├── classes/           # Classes PHP (Modèles)
    │   ├── Database.php   # Gestion de la connexion à la base de données
    │   ├── Task.php       # Gestion des tâches
    │   ├── User.php       # Gestion des utilisateurs
    │   └── Utility.php    # Fonctions utilitaires
    └── includes/          # Configuration
        └── config.php     # Configuration de l'application
```

### Principes d'architecture

L'application est organisée selon les principes MVC :

- **Modèles** : Classes PHP dans `src/classes/` qui encapsulent la logique métier et l'accès aux données
- **Vues** : Fichiers PHP dans `public/` qui gèrent l'affichage et l'interface utilisateur
- **Contrôleurs** : Scripts dans `src/actions/` qui traitent les requêtes et les actions utilisateur

## Rôles et Permissions

L'application implémente un système de contrôle d'accès basé sur deux profils utilisateur :

### Manager
- Peut créer, modifier et supprimer des tâches pour tous les membres de l'équipe
- Peut assigner des tâches à n'importe quel utilisateur
- A accès à toutes les fonctionnalités de l'application

### Collaborateur
- Peut créer des tâches mais ne peut les assigner qu'à lui-même
- Peut modifier et supprimer uniquement les tâches qu'il a créées ou qui lui sont assignées
- Peut voir les tâches des autres utilisateurs mais ne peut pas les modifier

### Règles spécifiques
- Les tâches marquées comme "Terminé" ne peuvent plus être modifiées, seulement supprimées
- Tous les utilisateurs peuvent consulter le calendrier et exporter leurs tâches en PDF

## Technologies utilisées

TacTâche est développé avec les technologies suivantes :

### Backend
- **PHP** : Programmation orientée objet pour la logique métier
- **MySQL** : Base de données relationnelle pour le stockage des données
- **PDO** : Requêtes préparées pour l'accès sécurisé à la base de données
- **TCPDF** : Génération de documents PDF pour l'exportation des tâches

### Frontend
- **HTML/CSS** : Interface utilisateur avec design responsive
- **JavaScript** : Interactivité côté client
- **Axios** : Bibliothèque pour les requêtes AJAX
- **FullCalendar** : Affichage des tâches dans un calendrier interactif
- **Font Awesome** : Icônes pour l'interface utilisateur

### Sécurité
- **Authentification** : Système de connexion sécurisé avec sessions PHP
- **Hachage des mots de passe** : Utilisation de password_hash()
- **Validation des données** : Vérification des entrées utilisateur
- **Contrôle d'accès** : Permissions basées sur les rôles utilisateur

## Installation

1. Clonez le dépôt sur votre serveur web (XAMPP recommandé)
2. Importez le fichier `database/init.sql` dans votre base de données MySQL
3. Configurez les paramètres de connexion à la base de données dans `src/includes/config.php`
4. Assurez-vous que le répertoire `public/uploads/tasks` est accessible en écriture par le serveur web
5. Accédez à l'application via votre navigateur à l'adresse correspondante (ex: http://localhost/To-Do-App/public)

## Utilisation

1. Créez un compte utilisateur ou connectez-vous avec un compte existant
2. Utilisez le tableau Kanban pour visualiser et gérer vos tâches
3. Consultez le calendrier pour voir la planification temporelle
4. Exportez vos tâches en PDF pour les partager ou les archiver
5. Ajoutez des photos à vos tâches pour une meilleure documentation
