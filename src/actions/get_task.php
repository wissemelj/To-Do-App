<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit();
}

$taskId = $_GET['id'] ?? null;

if (!$taskId) {
    echo json_encode(['success' => false, 'error' => 'ID manquant']);
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT tasks.*, users.username AS assigned_username 
        FROM tasks 
        LEFT JOIN users ON tasks.assigned_to = users.id 
        WHERE tasks.id = ?
    ");
    $stmt->execute([$taskId]);
    $task = $stmt->fetch();

    if (!$task) {
        echo json_encode(['success' => false, 'error' => 'Tâche introuvable']);
        exit();
    }

    // Formater la date pour le formulaire
    $task['due_date'] = $task['due_date'] 
        ? date('Y-m-d\TH:i', strtotime($task['due_date'])) 
        : null;

    echo json_encode([
        'success' => true,
        'data' => $task
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur BDD: ' . $e->getMessage()]);
}
?>