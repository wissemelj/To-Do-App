<?php
// Ce fichier établit la connexion à la base de données MySQL
// Il utilise PDO (PHP Data Objects) qui est une interface pour accéder à la base de données

// Inclure le fichier de configuration qui contient les informations de connexion
require_once __DIR__ . '/config.php';

try {
    // Créer une nouvelle connexion PDO
    // 1. La chaîne de connexion contient: le type de base de données (mysql), l'hôte, le nom de la base et le jeu de caractères
    // 2. Le nom d'utilisateur de la base de données
    // 3. Le mot de passe de la base de données
    // 4. Un tableau d'options pour configurer la connexion
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASSWORD,
        [
            // Cette option fait que PDO lancera des exceptions en cas d'erreur
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Cette option fait que les résultats seront retournés sous forme de tableaux associatifs
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    // En cas d'erreur de connexion, on affiche un message d'erreur
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}
?>
