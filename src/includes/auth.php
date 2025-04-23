<?php
/**
 * Authentication and authorization functions
 * Handles user sessions, login/logout, and permission checks
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session parameters
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_httponly', 1);

    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }

    session_start();
}

/**
 * Check if a user is currently logged in
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Log in a user by ID and store their role in session
 * @param int $userId The user ID to log in
 * @return void
 */
function loginUser(int $userId): void {
    global $pdo;

    // Store user ID in session
    $_SESSION['user_id'] = $userId;

    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);

    // Get and store user role in session
    $stmt = $pdo->prepare("SELECT role, username FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['username'] = $user['username'];
    }
}

/**
 * Log out the current user
 * @return void
 */
function logoutUser(): void {
    // Clear all session data
    $_SESSION = [];

    // Clear session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Destroy the session
    session_destroy();
}

/**
 * Get the ID of the currently logged in user
 * @return int|null User ID or null if not logged in
 */
function getLoggedInUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get the role of the currently logged in user
 * @return string|null User role or null if not set
 */
function getUserRole(): ?string {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Check if the current user is a manager
 * @return bool True if user is a manager, false otherwise
 */
function isManager(): bool {
    return getUserRole() === 'manager';
}

/**
 * Check if the current user is a collaborator
 * @return bool True if user is a collaborator, false otherwise
 */
function isCollaborator(): bool {
    return getUserRole() === 'collaborator';
}

/**
 * Check if the current user can modify a specific task
 * @param int $taskId The task ID to check
 * @return bool True if user can modify the task, false otherwise
 */
function canModifyTask(int $taskId): bool {
    global $pdo;
    $userId = getLoggedInUserId();

    // Managers can modify any task
    if (isManager()) {
        return true;
    }

    // Cache task permissions to avoid repeated database queries
    static $taskPermissionsCache = [];

    if (isset($taskPermissionsCache[$taskId])) {
        return $taskPermissionsCache[$taskId];
    }

    // Collaborators can modify tasks they created OR tasks assigned to them
    $stmt = $pdo->prepare("SELECT created_by, assigned_to FROM tasks WHERE id = ?");
    $stmt->execute([$taskId]);
    $task = $stmt->fetch();

    $canModify = $task && ($task['created_by'] === $userId || $task['assigned_to'] === $userId);

    // Cache the result
    $taskPermissionsCache[$taskId] = $canModify;

    return $canModify;
}

/**
 * Check if the current user can view a specific task
 * @param int $taskId The task ID to check
 * @return bool True if user can view the task, false otherwise
 */
function canViewTask(int $taskId): bool {
    global $pdo;

    // Cache task existence to avoid repeated database queries
    static $taskExistsCache = [];

    if (isset($taskExistsCache[$taskId])) {
        return $taskExistsCache[$taskId];
    }

    // Both managers and collaborators can view any task
    $stmt = $pdo->prepare("SELECT id FROM tasks WHERE id = ?");
    $stmt->execute([$taskId]);

    $exists = (bool) $stmt->fetch();

    // Cache the result
    $taskExistsCache[$taskId] = $exists;

    return $exists;
}

/**
 * Require the user to be logged in, redirect to login page if not
 * @return void
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        header("Location: " . SITE_URL . "/login.php");
        exit();
    }
}
?>