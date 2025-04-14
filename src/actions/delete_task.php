<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$taskId = $data['task_id'] ?? null;
$userId = getLoggedInUserId();

if (!$taskId) {
    echo json_encode(['success' => false, 'error' => 'ID manquant']);
    exit();
}

// delete_task.php (modified)
try {
    // Vérifier les permissions
    $stmt = $pdo->prepare("SELECT created_by FROM tasks WHERE id = ?");
    $stmt->execute([$taskId]);
    $task = $stmt->fetch();

    if (!$task || $task['created_by'] !== $userId) {
        echo json_encode(['success' => false, 'error' => 'Permission refusée']);
        exit();
    }

    // Suppression
    $pdo->beginTransaction();
    // Remove this line: $stmt = $pdo->prepare("DELETE FROM comments WHERE task_id = ?");
    
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->execute([$taskId]);
    
    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => 'Erreur BDD: ' . $e->getMessage()]);
}