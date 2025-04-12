<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode([]);
    exit();
}

$userId = getLoggedInUserId();

try {
    $stmt = $pdo->prepare("
        SELECT 
            id,
            title,
            due_date AS start,
            status
        FROM tasks
        WHERE created_by = ? OR assigned_to = ?
    ");
    
    $stmt->execute([$userId, $userId]);
    $tasks = $stmt->fetchAll();

    $events = [];
    foreach ($tasks as $task) {
        $events[] = [
            'id' => $task['id'],
            'title' => $task['title'],
            'start' => $task['start'],
            'allDay' => true,
            'extendedProps' => [
                'status' => $task['status']
            ]
        ];
    }

    echo json_encode($events);

} catch (PDOException $e) {
    echo json_encode([]);
}
?>