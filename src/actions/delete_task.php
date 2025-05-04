<?php
/**
 * Fichier: delete_task.php
 *
 * Ce fichier gère la suppression d'une tâche existante dans l'application.
 * Il reçoit l'ID de la tâche à supprimer via une requête AJAX, vérifie que l'utilisateur
 * est authentifié et a les permissions nécessaires (seuls les managers ou les créateurs
 * de la tâche peuvent la supprimer), puis supprime la tâche de la base de données.
 *
 * Méthode HTTP: POST
 * Format de données: JSON
 * Réponse: JSON
 */
require_once __DIR__ . '/../includes/config.php';

// Définit le type de contenu de la réponse comme JSON
header('Content-Type: application/json');

// Vérifie si l'utilisateur est connecté
// Si non, renvoie une erreur et arrête l'exécution du script
if (!$userObj->isLoggedIn()) {
    Utility::jsonResponse(['success' => false, 'error' => 'Non authentifié']);
}

// Récupère les données JSON envoyées dans le corps de la requête
// Extrait l'ID de la tâche à supprimer
$data = json_decode(file_get_contents('php://input'), true);
$taskId = $data['task_id'] ?? null;

// Vérifie que l'ID de la tâche est présent
// Si non, renvoie une erreur et arrête l'exécution du script
if (!$taskId) {
    Utility::jsonResponse(['success' => false, 'error' => 'ID manquant']);
}

// Récupère les informations de propriété de la tâche (créateur et assigné)
// Ces informations sont utilisées pour vérifier les permissions
$task = $taskObj->getTaskOwnership((int)$taskId);

// Vérifie si la tâche existe
// Si non, renvoie une erreur et arrête l'exécution du script
if (!$task) {
    Utility::jsonResponse(['success' => false, 'error' => 'Tâche introuvable']);
}

// Vérifie si l'utilisateur a le droit de supprimer cette tâche
// Seuls les managers ou les créateurs de la tâche peuvent la supprimer
if (!$userObj->isManager() && $task['created_by'] != $userObj->getLoggedInUserId()) {
    Utility::jsonResponse([
        'success' => false,
        'error' => 'Permission refusée: Seul le créateur ou un manager peut supprimer cette tâche'
    ]);
}

// Supprime la tâche de la base de données en utilisant la méthode deleteTask de la classe Task
// Le résultat est un booléen indiquant si la suppression a réussi
$result = $taskObj->deleteTask((int)$taskId);

// Renvoie le résultat au format JSON
// success: true si la suppression a réussi, false sinon
Utility::jsonResponse(['success' => $result]);