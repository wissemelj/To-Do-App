<?php
/**
 * Fichier de configuration principal de l'application
 *
 * Ce fichier contient les paramètres de configuration essentiels pour l'application :
 * - Configuration de l'affichage des erreurs
 * - Paramètres de connexion à la base de données
 * - Autoloading des classes
 * - Initialisation des objets principaux
 *
 * Ce fichier est inclus dans toutes les pages de l'application.
 */

// Configuration de l'affichage des erreurs
// En développement, il est utile d'afficher toutes les erreurs pour faciliter le débogage
// En production, ces lignes devraient être commentées ou modifiées
error_reporting(E_ALL);  // Rapporte toutes les erreurs PHP
ini_set('display_errors', 1);  // Affiche les erreurs à l'écran

// Définition des constantes de configuration
// Ces constantes sont utilisées pour la connexion à la base de données et les URLs
define('DB_HOST', 'localhost');     // Hôte de la base de données (généralement localhost)
define('DB_NAME', 'task_manager');  // Nom de la base de données
define('DB_USER', 'root');          // Nom d'utilisateur MySQL (root par défaut pour XAMPP)
define('DB_PASSWORD', '');          // Mot de passe MySQL (vide par défaut pour XAMPP)
define('SITE_URL', 'http://localhost/To-Do-App/public');  // URL de base du site

/**
 * Configuration de l'autoloading des classes
 *
 * Cette fonction anonyme est enregistrée comme gestionnaire d'autoloading.
 * Lorsqu'une classe est utilisée mais n'est pas encore chargée, PHP appelle
 * cette fonction avec le nom de la classe comme paramètre.
 *
 * La fonction recherche alors le fichier correspondant dans le dossier classes
 * et l'inclut s'il existe.
 */
spl_autoload_register(function ($class_name) {
    // Construit le chemin vers le fichier de classe
    // __DIR__ est le dossier du fichier actuel (includes)
    $file = __DIR__ . '/../classes/' . $class_name . '.php';

    // Vérifie si le fichier existe avant de l'inclure
    if (file_exists($file)) {
        require_once $file;  // Inclut le fichier de classe
    }
});

// Initialisation de la connexion à la base de données
// Crée une instance de la classe Database avec les paramètres définis plus haut
$db = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD);
// Récupère l'objet PDO qui sera utilisé pour toutes les requêtes
$pdo = $db->getConnection();

// Initialisation des objets principaux de l'application
// Ces objets seront disponibles dans toutes les pages qui incluent ce fichier
$userObj = new User($pdo);  // Objet pour gérer les utilisateurs (authentification, etc.)
$taskObj = new Task($pdo);  // Objet pour gérer les tâches (création, modification, etc.)
?>