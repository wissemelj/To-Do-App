<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false]);
    exit();
}

$taskId = $_POST['task_id'] ?? null;
$content = trim($_POST['content'] ?? '');
$userId = getLoggedInUserId();

if (!$taskId || empty($content)) {
    echo json_encode(['success' => false, 'error' => 'Données invalides']);
    exit();
}

try {
    $stmt = $pdo->prepare("INSERT INTO comments (task_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$taskId, $userId, $content]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur BDD']);
}