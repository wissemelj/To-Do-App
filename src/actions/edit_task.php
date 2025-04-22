<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Accès non autorisé']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

// Validation
$requiredFields = ['id', 'title'];
foreach ($requiredFields as $field) {
    if (empty($input[$field])) {
        echo json_encode(['success' => false, 'error' => 'Champ ' . $field . ' manquant']);
        exit();
    }
}

try {
    // Vérifier les permissions avec la nouvelle fonction canModifyTask
    if (!canModifyTask((int)$input['id'])) {
        echo json_encode(['success' => false, 'error' => 'Permission refusée']);
        exit();
    }

    // Préparation des données
    $dueDate = !empty($input['due_date'])
        ? date('Y-m-d H:i:s', strtotime($input['due_date']))
        : null;

    // Mise à jour
    $stmt = $pdo->prepare("
        UPDATE tasks SET
            title = :title,
            description = :description,
            status = :status,
            due_date = :due_date,
            assigned_to = :assigned_to
        WHERE id = :id
    ");

    $stmt->execute([
        ':title' => $input['title'],
        ':description' => $input['description'] ?? null,
        ':status' => $input['status'] ?? 'todo',
        ':due_date' => $dueDate,
        ':assigned_to' => $input['assigned_to'] ?? null,
        ':id' => (int)$input['id']
    ]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    error_log('Erreur BDD: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Erreur de mise à jour: ' . $e->getMessage()
    ]);
}
?>