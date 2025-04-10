<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit();
}

$usernameOrEmail = trim($_POST['username_or_email']);
$password = $_POST['password'];

// Validation
if (empty($usernameOrEmail) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Tous les champs sont obligatoires']);
    exit();
}

// Chercher l'utilisateur
$stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = ? OR email = ?");
$stmt->execute([$usernameOrEmail, $usernameOrEmail]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'error' => 'Identifiants incorrects']);
    exit();
}

// Connecter l'utilisateur
loginUser($user['id']);
echo json_encode(['success' => true]);