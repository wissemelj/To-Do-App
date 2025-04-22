<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function loginUser(int $userId): void {
    global $pdo;

    // Store user ID in session
    $_SESSION['user_id'] = $userId;

    // Get and store user role in session
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_role'] = $user['role'];
    }
}

function logoutUser(): void {
    session_unset();
    session_destroy();
}

function getLoggedInUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

function getUserRole(): ?string {
    return $_SESSION['user_role'] ?? null;
}

function isManager(): bool {
    return getUserRole() === 'manager';
}

function isCollaborator(): bool {
    return getUserRole() === 'collaborator';
}

function canModifyTask(int $taskId): bool {
    global $pdo;
    $userId = getLoggedInUserId();

    // Managers can modify any task
    if (isManager()) {
        return true;
    }

    // Collaborators can modify tasks they created OR tasks assigned to them
    $stmt = $pdo->prepare("SELECT created_by, assigned_to FROM tasks WHERE id = ?");
    $stmt->execute([$taskId]);
    $task = $stmt->fetch();

    return $task && ($task['created_by'] === $userId || $task['assigned_to'] === $userId);
}

function canViewTask(int $taskId): bool {
    global $pdo;
    $userId = getLoggedInUserId();

    // Both managers and collaborators can view any task
    $stmt = $pdo->prepare("SELECT id FROM tasks WHERE id = ?");
    $stmt->execute([$taskId]);

    return (bool) $stmt->fetch();
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header("Location: " . SITE_URL . "/login.php");
        exit();
    }
}
?>