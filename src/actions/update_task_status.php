<?php
/**
 * Fichier: update_task_status.php
 *
 * Ce fichier gère la mise à jour de la date d'échéance d'une tâche lors du déplacement
 * dans la vue calendrier. Il reçoit l'ID de la tâche et la nouvelle date via une requête AJAX,
 * vérifie que l'utilisateur est authentifié, puis met à jour la date d'échéance dans la base de données.
 *
 * Cette fonctionnalité est utilisée lorsqu'un utilisateur déplace une tâche dans le calendrier
 * par glisser-déposer, ce qui modifie sa date d'échéance.
 *
 * Méthode HTTP: POST
 * Format de données: JSON
 * Paramètres:
 *   - task_id: ID de la tâche à mettre à jour
 *   - new_date: Nouvelle date d'échéance
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
// file_get_contents('php://input') lit les données brutes du corps de la requête
// json_decode convertit la chaîne JSON en tableau associatif PHP
$data = json_decode(file_get_contents('php://input'), true);

try {
    // Prépare la requête SQL pour mettre à jour la date d'échéance de la tâche
    // Utilise des paramètres liés pour éviter les injections SQL
    $stmt = $pdo->prepare("
        UPDATE tasks
        SET due_date = ?
        WHERE id = ?
    ");

    // Exécute la requête avec les paramètres
    // Convertit la date reçue au format de la base de données (Y-m-d H:i:s)
    $stmt->execute([
        date('Y-m-d H:i:s', strtotime($data['new_date'])),  // Nouvelle date d'échéance
        $data['task_id']                                    // ID de la tâche
    ]);

    // Renvoie un message de succès au format JSON
    Utility::jsonResponse(['success' => true]);

} catch (PDOException $e) {
    // En cas d'erreur, renvoie un message d'erreur au format JSON
    Utility::jsonResponse(['success' => false, 'error' => 'Erreur lors de la mise à jour']);
}
?>