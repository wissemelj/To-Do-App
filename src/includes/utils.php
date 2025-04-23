<?php
function ensureUsernameInSession(PDO $pdo, int $userId): void {
    if (!isset($_SESSION['username']) && $userId) {
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        $_SESSION['username'] = $user ? $user['username'] : 'Utilisateur';
    }
}

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

function getStatusLabels(): array {
    return [
        'todo' => 'À Faire',
        'in_progress' => 'En Cours',
        'done' => 'Terminé'
    ];
}

function formatDate(?string $dateString, string $format = 'd/m/Y H:i'): string {
    return $dateString ? date($format, strtotime($dateString)) : '';
}

function h(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
?>
