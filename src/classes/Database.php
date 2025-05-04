<?php
/**
 * Classe Database
 *
 * Cette classe gère la connexion à la base de données MySQL.
 * Elle utilise PDO (PHP Data Objects) pour établir la connexion.
 * La classe est conçue pour être simple et facile à comprendre.
 */
class Database {
    /**
     * Stocke l'objet PDO qui représente la connexion à la base de données
     * Cette propriété est privée pour empêcher l'accès direct depuis l'extérieur de la classe
     */
    private $pdo;

    /**
     * Constructeur de la classe Database
     *
     * Ce constructeur est appelé automatiquement lorsqu'on crée une nouvelle instance de Database.
     * Il établit la connexion à la base de données avec les paramètres fournis.
     *
     * @param string $host     L'hôte de la base de données (généralement 'localhost')
     * @param string $dbname   Le nom de la base de données à laquelle se connecter
     * @param string $user     Le nom d'utilisateur pour la connexion
     * @param string $password Le mot de passe pour la connexion
     */
    public function __construct(string $host, string $dbname, string $user, string $password) {
        try {
            // Création d'un nouvel objet PDO pour la connexion à la base de données
            // Le DSN (Data Source Name) contient les informations de connexion
            $this->pdo = new PDO(
                "mysql:host={$host};dbname={$dbname}", // Chaîne de connexion avec hôte et nom de la base
                $user,                                 // Nom d'utilisateur MySQL
                $password,                             // Mot de passe MySQL
                [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC] // Configuration pour retourner les résultats sous forme de tableaux associatifs
            );
        } catch (PDOException $e) {
            // En cas d'erreur de connexion, arrête le script et affiche le message d'erreur
            die("Erreur de connexion à la base de données: " . $e->getMessage());
        }
    }

    /**
     * Méthode pour obtenir l'objet PDO
     *
     * Cette méthode permet d'accéder à l'objet PDO depuis l'extérieur de la classe
     * pour exécuter des requêtes SQL.
     *
     * @return PDO L'objet PDO représentant la connexion à la base de données
     */
    public function getConnection(): PDO {
        return $this->pdo;
    }
}
