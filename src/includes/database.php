<?php
/**
 * Database connection setup
 * Establishes a PDO connection to the MySQL database with optimized settings
 */
require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            // Optimized connection settings
            PDO::ATTR_PERSISTENT => true, // Use persistent connections
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true, // Use buffered queries
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci" // Ensure proper UTF-8 handling
        ]
    );
} catch (PDOException $e) {
    // Log error and display user-friendly message
    error_log("Database connection error: " . $e->getMessage());
    die("Erreur de connexion à la base de données. Veuillez réessayer plus tard.");
}
?>
