<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/database.php';

requireLogin();

header('Content-Type: application/json');

try {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception('ID de tâche invalide');
    }

    $userId = getLoggedInUserId();
    $taskId = (int)$_GET['id'];

    // Tous les utilisateurs peuvent voir toutes les tâches dans le calendrier
    $stmt = $pdo->prepare("
        SELECT
            tasks.*,
            assignee.username AS assigned_username,
            creator.username AS creator_username
        FROM tasks
        LEFT JOIN users AS assignee ON tasks.assigned_to = assignee.id
        INNER JOIN users AS creator ON tasks.created_by = creator.id
        WHERE tasks.id = ?
    ");
    $stmt->execute([$taskId]);

    $task = $stmt->fetch();

    if (!$task) {
        throw new Exception('Tâche non trouvée');
    }

    echo json_encode([
        'success' => true,
        'task' => [
            'title' => $task['title'],
            'description' => $task['description'],
            'status' => $task['status'],
            'due_date' => $task['due_date'],
            'assigned_username' => $task['assigned_username'],
            'creator_username' => $task['creator_username']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}