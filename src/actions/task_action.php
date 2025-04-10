<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit();
}

// Récupération des données
$data = json_decode(file_get_contents('php://input'), true);

$title = trim($data['title'] ?? '');
$description = trim($data['description'] ?? '');
$assignedTo = $data['assigned_to'] ?? null;
$dueDate = $data['due_date'] ?? null;
$userId = getLoggedInUserId();

// Validation
if (empty($title)) {
    echo json_encode(['success' => false, 'error' => 'Le titre est obligatoire']);
    exit();
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO tasks 
        (title, description, created_by, assigned_to, due_date, status)
        VALUES (:title, :description, :user_id, :assigned_to, :due_date, 'todo')
    ");

    $stmt->execute([
        ':title' => $title,
        ':description' => $description,
        ':user_id' => $userId,
        ':assigned_to' => $assignedTo ?: null,
        ':due_date' => $dueDate ? date('Y-m-d H:i:s', strtotime($dueDate)) : null
    ]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    error_log('Erreur BDD : ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => 'Erreur de base de données : ' . $e->getMessage()
    ]);
}
?>