<?php
// Ce fichier contient des fonctions utilitaires utilisées dans toute l'application

// S'assure que le nom d'utilisateur est stocké dans la session
// Si le nom d'utilisateur n'est pas dans la session, on le récupère depuis la base de données
function ensureUsernameInSession(PDO $pdo, int $userId): void {
    if (!isset($_SESSION['username']) && $userId) {
        // Préparer et exécuter la requête pour obtenir le nom d'utilisateur
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        // Stocker le nom d'utilisateur dans la session
        $_SESSION['username'] = $user ? $user['username'] : 'Utilisateur';
    }
}

// Récupère toutes les tâches et les organise par statut (À faire, En cours, Terminé)
function getTasksByStatus(PDO $pdo): array {
    // Cette requête récupère toutes les tâches avec les noms des utilisateurs associés
    $stmt = $pdo->prepare("
        SELECT tasks.*,
               users.username AS assigned_username,
               creator.username AS creator_username
        FROM tasks
        LEFT JOIN users ON tasks.assigned_to = users.id
        LEFT JOIN users AS creator ON tasks.created_by = creator.id
        ORDER BY tasks.due_date ASC
    ");
    $stmt->execute();
    $allTasks = $stmt->fetchAll();

    // Initialiser un tableau pour stocker les tâches par statut
    $tasksByStatus = [
        'todo' => [],        // Tâches à faire
        'in_progress' => [], // Tâches en cours
        'done' => []         // Tâches terminées
    ];

    // Répartir les tâches dans les catégories appropriées
    foreach ($allTasks as $task) {
        $tasksByStatus[$task['status']][] = $task;
    }

    return $tasksByStatus;
}

// Retourne les libellés des statuts de tâches
function getStatusLabels(): array {
    return [
        'todo' => 'À Faire',
        'in_progress' => 'En Cours',
        'done' => 'Terminé'
    ];
}

// Formate une date pour l'affichage
// Si la date est null, retourne une chaîne vide
function formatDate(?string $dateString, string $format = 'd/m/Y H:i'): string {
    return $dateString ? date($format, strtotime($dateString)) : '';
}

// Fonction de raccourci pour htmlspecialchars
// Sécurise les données avant de les afficher dans le HTML pour éviter les attaques XSS
function h(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
?>
