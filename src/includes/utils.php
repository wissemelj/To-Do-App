<?php
/**
 * Utility functions for the Task Manager application
 */

/**
 * Ensures the user's username is stored in the session
 * @param PDO $pdo Database connection
 * @param int $userId User ID
 * @return void
 */
function ensureUsernameInSession(PDO $pdo, int $userId): void {
    if (!isset($_SESSION['username']) && $userId) {
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if ($user) {
            $_SESSION['username'] = $user['username'];
        } else {
            // Fallback au cas où l'utilisateur n'est pas trouvé
            $_SESSION['username'] = 'Utilisateur';
        }
    }
}

/**
 * Get tasks organized by status
 * @param PDO $pdo Database connection
 * @return array Associative array of tasks by status
 */
function getTasksByStatus(PDO $pdo): array {
    $stmt = $pdo->prepare("
        SELECT tasks.*, users.username AS assigned_username, creator.username AS creator_username
        FROM tasks
        LEFT JOIN users ON tasks.assigned_to = users.id
        LEFT JOIN users AS creator ON tasks.created_by = creator.id
        ORDER BY tasks.due_date ASC
    ");
    $stmt->execute();
    $allTasks = $stmt->fetchAll();
    
    $tasksByStatus = [
        'todo' => [],
        'in_progress' => [],
        'done' => []
    ];
    
    foreach ($allTasks as $task) {
        $tasksByStatus[$task['status']][] = $task;
    }
    
    return $tasksByStatus;
}

/**
 * Get status labels
 * @return array Associative array of status labels
 */
function getStatusLabels(): array {
    return [
        'todo' => 'À Faire',
        'in_progress' => 'En Cours',
        'done' => 'Terminé'
    ];
}

/**
 * Format date for display
 * @param string|null $dateString Date string from database
 * @param string $format Format string for date
 * @return string Formatted date or empty string if date is null
 */
function formatDate(?string $dateString, string $format = 'd/m/Y H:i'): string {
    if (!$dateString) {
        return '';
    }
    return date($format, strtotime($dateString));
}

/**
 * Sanitize output for HTML display
 * @param string|null $string String to sanitize
 * @return string Sanitized string
 */
function h(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
?>
