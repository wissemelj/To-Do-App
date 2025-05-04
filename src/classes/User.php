<?php
/**
 * Classe User
 *
 * Cette classe gère tout ce qui concerne les utilisateurs dans l'application :
 * - Authentification (connexion/déconnexion)
 * - Inscription de nouveaux utilisateurs
 * - Gestion des sessions
 * - Vérification des permissions
 *
 * La classe est conçue pour être simple et facile à comprendre.
 */
class User {
    /**
     * Stocke l'objet PDO pour accéder à la base de données
     * Cette propriété est utilisée dans toutes les méthodes qui ont besoin
     * d'interagir avec la base de données.
     */
    private $pdo;

    /**
     * Constructeur de la classe User
     *
     * Initialise l'objet User avec une connexion à la base de données
     * et démarre une session si aucune n'est active.
     *
     * @param PDO $pdo L'objet PDO représentant la connexion à la base de données
     */
    public function __construct(PDO $pdo) {
        // Stocke la connexion à la base de données pour une utilisation ultérieure
        $this->pdo = $pdo;

        // Démarre une session PHP si aucune n'est active
        // Les sessions permettent de stocker des données utilisateur entre les requêtes
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Vérifie si un utilisateur est actuellement connecté
     *
     * Cette méthode vérifie si l'ID utilisateur est présent dans la session,
     * ce qui indique qu'un utilisateur est connecté.
     *
     * @return bool Vrai si un utilisateur est connecté, faux sinon
     */
    public function isLoggedIn(): bool {
        // Vérifie si la clé 'user_id' existe dans le tableau $_SESSION
        return isset($_SESSION['user_id']);
    }

    /**
     * Récupère l'ID de l'utilisateur actuellement connecté
     *
     * @return int|null L'ID de l'utilisateur connecté ou null si aucun utilisateur n'est connecté
     */
    public function getLoggedInUserId(): ?int {
        // Retourne l'ID utilisateur stocké dans la session ou null s'il n'existe pas
        // L'opérateur ?? est l'opérateur de fusion null (null coalescing)
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Récupère le rôle de l'utilisateur connecté
     *
     * Les rôles possibles sont 'manager' ou 'collaborator'.
     *
     * @return string|null Le rôle de l'utilisateur ou null si non connecté
     */
    public function getUserRole(): ?string {
        // Retourne le rôle utilisateur stocké dans la session ou null s'il n'existe pas
        return $_SESSION['user_role'] ?? null;
    }

    /**
     * Vérifie si l'utilisateur connecté est un manager
     *
     * Les managers ont des privilèges étendus dans l'application.
     *
     * @return bool Vrai si l'utilisateur est un manager, faux sinon
     */
    public function isManager(): bool {
        // Compare le rôle de l'utilisateur avec 'manager'
        return $this->getUserRole() === 'manager';
    }

    /**
     * Vérifie si l'utilisateur connecté est un collaborateur
     *
     * @return bool Vrai si l'utilisateur est un collaborateur, faux sinon
     */
    public function isCollaborator(): bool {
        // Compare le rôle de l'utilisateur avec 'collaborator'
        return $this->getUserRole() === 'collaborator';
    }

    /**
     * Connecte un utilisateur en créant une session
     *
     * Cette méthode est appelée après une authentification réussie.
     * Elle stocke les informations de l'utilisateur dans la session.
     *
     * @param int $userId L'ID de l'utilisateur à connecter
     */
    public function loginUser(int $userId): void {
        // Stocke l'ID utilisateur dans la session
        $_SESSION['user_id'] = $userId;

        // Prépare et exécute la requête SQL avec un paramètre lié pour plus de sécurité
        $stmt = $this->pdo->prepare("SELECT role, username FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        // Si l'utilisateur existe, stocke son rôle et son nom d'utilisateur dans la session
        if ($user) {
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
        }
    }

    /**
     * Déconnecte l'utilisateur en détruisant la session
     */
    public function logoutUser(): void {
        // Vide le tableau de session
        $_SESSION = [];

        // Détruit complètement la session
        session_destroy();
    }

    /**
     * S'assure que le nom d'utilisateur est stocké dans la session
     *
     * Cette méthode est utile pour récupérer le nom d'utilisateur
     * si celui-ci n'est pas déjà dans la session.
     */
    public function ensureUsernameInSession(): void {
        // Récupère l'ID de l'utilisateur connecté
        $userId = $this->getLoggedInUserId();

        // Si le nom d'utilisateur n'est pas dans la session mais que l'utilisateur est connecté
        if (!isset($_SESSION['username']) && $userId) {
            // Prépare et exécute la requête SQL avec un paramètre lié pour plus de sécurité
            $stmt = $this->pdo->prepare("SELECT username FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $username = $stmt->fetchColumn();

            // Stocke le nom d'utilisateur dans la session ou 'Utilisateur' par défaut
            $_SESSION['username'] = $username ?: 'Utilisateur';
        }
    }

    /**
     * Vérifie si l'utilisateur peut modifier une tâche
     *
     * Dans cette version simplifiée, tous les utilisateurs peuvent modifier toutes les tâches.
     *
     * @param int $taskId L'ID de la tâche
     * @param Task $taskObj L'objet Task pour accéder aux méthodes de tâche
     * @return bool Toujours vrai dans cette version simplifiée
     */
    public function canModifyTask(int $taskId, Task $taskObj): bool {
        // Dans cette version simplifiée, tout le monde peut modifier toutes les tâches
        return true;
    }

    /**
     * Vérifie si l'utilisateur peut voir une tâche
     *
     * Dans cette version simplifiée, tous les utilisateurs peuvent voir toutes les tâches.
     *
     * @param int $taskId L'ID de la tâche
     * @param Task $taskObj L'objet Task pour accéder aux méthodes de tâche
     * @return bool Toujours vrai dans cette version simplifiée
     */
    public function canViewTask(int $taskId, Task $taskObj): bool {
        // Dans cette version simplifiée, tout le monde peut voir toutes les tâches
        return true;
    }

    /**
     * Exige que l'utilisateur soit connecté, sinon redirige vers la page de connexion
     *
     * Cette méthode est utilisée pour protéger les pages qui nécessitent une connexion.
     *
     * @param string $loginUrl L'URL de la page de connexion
     */
    public function requireLogin(string $loginUrl): void {
        // Si l'utilisateur n'est pas connecté
        if (!$this->isLoggedIn()) {
            // Redirige vers la page de connexion
            header("Location: " . $loginUrl);
            // Arrête l'exécution du script
            exit();
        }
    }

    /**
     * Récupère la liste de tous les utilisateurs
     *
     * @return array Un tableau contenant tous les utilisateurs avec leur ID, nom et rôle
     */
    public function getAllUsers(): array {
        // Prépare et exécute une requête SQL pour récupérer tous les utilisateurs triés par nom
        $stmt = $this->pdo->prepare("SELECT id, username, role FROM users ORDER BY username");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Authentifie un utilisateur
     *
     * Cette méthode vérifie les identifiants fournis et connecte l'utilisateur si valides.
     *
     * @param string $usernameOrEmail Le nom d'utilisateur ou l'email
     * @param string $password Le mot de passe
     * @return array Un tableau avec le résultat de l'authentification (succès ou erreur)
     */
    public function authenticate(string $usernameOrEmail, string $password): array {
        // Prépare et exécute une requête SQL pour trouver l'utilisateur
        $stmt = $this->pdo->prepare("SELECT id, password FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
        $user = $stmt->fetch();

        // Si l'utilisateur n'existe pas
        if (!$user) {
            return ['success' => false, 'error' => 'Utilisateur non trouvé'];
        }

        // Vérifie si le mot de passe fourni correspond au hash stocké dans la base de données
        // La fonction password_verify compare un mot de passe en clair avec un hash
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'error' => 'Mot de passe incorrect'];
        }

        // Si les identifiants sont corrects, connecte l'utilisateur
        $this->loginUser($user['id']);

        // Retourne un succès
        return ['success' => true];
    }

    /**
     * Inscrit un nouvel utilisateur
     *
     * Cette méthode crée un nouvel utilisateur dans la base de données.
     *
     * @param array $userData Les données de l'utilisateur à inscrire
     * @return array Un tableau avec le résultat de l'inscription (succès ou erreur)
     */
    public function register(array $userData): array {
        try {
            // Récupère les données de l'utilisateur
            $username = $userData['username'];
            $email = $userData['email'];

            // Vérifie si le nom d'utilisateur ou l'email existe déjà
            $exists = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
            $exists->execute([$username, $email]);

            // Si un utilisateur avec ce nom ou cet email existe déjà
            if ($exists->fetchColumn() > 0) {
                return ['success' => false, 'error' => 'Nom d\'utilisateur ou email déjà utilisé'];
            }

            // Hachage du mot de passe pour le stockage sécurisé
            // La fonction password_hash crée un hash sécurisé du mot de passe
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);

            // Définit le rôle de l'utilisateur (collaborator par défaut)
            $role = $userData['role'] ?? 'collaborator';

            // Prépare et exécute la requête SQL pour insérer le nouvel utilisateur
            $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashedPassword, $role]);

            // Retourne un succès
            return ['success' => true];
        } catch (PDOException $e) {
            // En cas d'erreur, retourne un message d'erreur
            return ['success' => false, 'error' => 'Erreur lors de l\'inscription'];
        }
    }
}
