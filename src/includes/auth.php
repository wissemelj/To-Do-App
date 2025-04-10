<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function loginUser(int $userId): void {
    $_SESSION['user_id'] = $userId;
}

function logoutUser(): void {
    session_unset();
    session_destroy();
}

function getLoggedInUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header("Location: " . SITE_URL . "/login.php");
        exit();
    }
}
?>