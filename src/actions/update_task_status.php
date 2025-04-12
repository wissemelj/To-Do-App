<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false]);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

try {
    $stmt = $pdo->prepare("
        UPDATE tasks
        SET due_date = ?
        WHERE id = ?
    ");
    
    $stmt->execute([
        date('Y-m-d H:i:s', strtotime($data['new_date'])),
        $data['task_id']
    ]);
    
    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['success' => false]);
}
?>