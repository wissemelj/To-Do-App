<?php
// Ce fichier contient toutes les fonctions liées à l'authentification et aux permissions
// Il gère les sessions utilisateur, la connexion/déconnexion, et les vérifications de permissions

// Inclure les fichiers nécessaires
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    // Paramètres de base pour la sécurité des sessions
    ini_set('session.cookie_httponly', 1); // Empêche l'accès aux cookies via JavaScript
    session_start();
}

// Vérifie si un utilisateur est connecté
function isLoggedIn(): bool {
    // Si l'ID utilisateur existe dans la session, l'utilisateur est connecté
    return isset($_SESSION['user_id']);
}

// Connecte un utilisateur et stocke ses informations dans la session
function loginUser(int $userId): void {
    global $pdo;

    // Stocker l'ID utilisateur dans la session
    $_SESSION['user_id'] = $userId;

    // Régénérer l'ID de session pour plus de sécurité
    session_regenerate_id(true);

    // Récupérer et stocker le rôle et le nom d'utilisateur
    $stmt = $pdo->prepare("SELECT role, username FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['username'] = $user['username'];
    }
}

// Déconnecte l'utilisateur actuel
function logoutUser(): void {
    // Vider toutes les données de session
    $_SESSION = [];

    // Détruire la session
    session_destroy();
}

// Récupère l'ID de l'utilisateur connecté
function getLoggedInUserId(): ?int {
    // Retourne l'ID utilisateur s'il existe, sinon null
    return $_SESSION['user_id'] ?? null;
}

// Récupère le rôle de l'utilisateur connecté
function getUserRole(): ?string {
    // Retourne le rôle utilisateur s'il existe, sinon null
    return $_SESSION['user_role'] ?? null;
}

// Vérifie si l'utilisateur est un manager
function isManager(): bool {
    return getUserRole() === 'manager';
}

// Vérifie si l'utilisateur est un collaborateur
function isCollaborator(): bool {
    return getUserRole() === 'collaborator';
}

// Vérifie si l'utilisateur peut modifier une tâche spécifique
function canModifyTask(int $taskId): bool {
    global $pdo;
    $userId = getLoggedInUserId();

    // Les managers peuvent modifier n'importe quelle tâche
    if (isManager()) {
        return true;
    }

    // Les collaborateurs peuvent modifier les tâches qu'ils ont créées OU qui leur sont assignées
    $stmt = $pdo->prepare("SELECT created_by, assigned_to FROM tasks WHERE id = ?");
    $stmt->execute([$taskId]);
    $task = $stmt->fetch();

    // Retourne vrai si la tâche existe et si l'utilisateur l'a créée ou si elle lui est assignée
    return $task && ($task['created_by'] === $userId || $task['assigned_to'] === $userId);
}

// Vérifie si l'utilisateur peut voir une tâche spécifique
function canViewTask(int $taskId): bool {
    global $pdo;

    // Les managers et les collaborateurs peuvent voir n'importe quelle tâche
    // On vérifie juste si la tâche existe
    $stmt = $pdo->prepare("SELECT id FROM tasks WHERE id = ?");
    $stmt->execute([$taskId]);

    return (bool) $stmt->fetch();
}

// Exige que l'utilisateur soit connecté, sinon redirige vers la page de connexion
function requireLogin(): void {
    if (!isLoggedIn()) {
        header("Location: " . SITE_URL . "/login.php");
        exit();
    }
}
?>