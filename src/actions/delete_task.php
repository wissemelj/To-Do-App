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
    // Vérifier si l'utilisateur est un manager ou le créateur de la tâche
    $stmt = $pdo->prepare("SELECT created_by FROM tasks WHERE id = ?");
    $stmt->execute([$taskId]);
    $task = $stmt->fetch();

    if (!$task) {
        echo json_encode(['success' => false, 'error' => 'Tâche introuvable']);
        exit();
    }

    // Seuls les managers ou le créateur de la tâche peuvent la supprimer
    if (!isManager() && $task['created_by'] != $userId) {
        echo json_encode(['success' => false, 'error' => 'Permission refusée: Seul le créateur ou un manager peut supprimer cette tâche']);
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