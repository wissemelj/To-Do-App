<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit();
}

try {
    $stmt = $pdo->query("SELECT id, username FROM users");
    $users = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $users
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erreur BDD: ' . $e->getMessage()
    ]);
}
?>