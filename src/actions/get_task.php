<?php
/**
 * Fichier: get_task.php
 *
 * Ce fichier gère la récupération des détails d'une tâche spécifique dans l'application.
 * Il reçoit l'ID de la tâche via une requête GET, vérifie que l'utilisateur
 * est authentifié et a les permissions nécessaires pour voir cette tâche,
 * puis renvoie les détails de la tâche au format JSON.
 *
 * Ce fichier peut être utilisé dans deux contextes différents :
 * 1. Pour récupérer tous les détails d'une tâche pour l'édition (mode par défaut)
 * 2. Pour récupérer un sous-ensemble des détails pour l'affichage dans le calendrier (mode 'calendar')
 *
 * Méthode HTTP: GET
 * Paramètres:
 *   - id: ID de la tâche à récupérer (obligatoire)
 *   - mode: Mode de récupération ('edit' par défaut ou 'calendar')
 * Réponse: JSON
 */
require_once __DIR__ . '/../includes/config.php';

// Définit le type de contenu de la réponse comme JSON
header('Content-Type: application/json');

try {
    // Vérifie si l'utilisateur est connecté
    if (!$userObj->isLoggedIn()) {
        throw new Exception('Non authentifié');
    }

    // Récupère l'ID de la tâche depuis les paramètres GET de la requête
    $taskId = $_GET['id'] ?? null;

    // Récupère le mode de récupération (edit par défaut ou calendar)
    $mode = $_GET['mode'] ?? 'edit';

    // Vérifie que l'ID de la tâche est présent et numérique
    if (!$taskId || !is_numeric($taskId)) {
        throw new Exception('ID de tâche invalide ou manquant');
    }

    // Convertit l'ID en entier pour éviter les injections SQL
    $taskId = (int)$taskId;

    // Vérifie si l'utilisateur a le droit de voir cette tâche
    // Cette vérification utilise la méthode canViewTask de la classe User
    if (!$userObj->canViewTask($taskId, $taskObj)) {
        throw new Exception('Tâche introuvable ou accès non autorisé');
    }

    // Récupère les détails de la tâche en utilisant la méthode getTask de la classe Task
    $task = $taskObj->getTask($taskId);

    // Vérifie si la tâche existe
    if (!$task) {
        throw new Exception('Tâche introuvable');
    }

    // Prépare la réponse en fonction du mode
    if ($mode === 'calendar') {
        // Mode calendrier : renvoie un sous-ensemble des données pour l'affichage
        Utility::jsonResponse([
            'success' => true,
            'task' => [
                'title' => $task['title'],               // Titre de la tâche
                'description' => $task['description'],   // Description de la tâche
                'status' => $task['status'],             // Statut de la tâche
                'due_date' => $task['due_date'],         // Date d'échéance
                'assigned_username' => $task['assigned_username'], // Nom de l'utilisateur assigné
                'creator_username' => $task['creator_username']    // Nom du créateur
            ]
        ]);
    } else {
        // Mode édition (par défaut) : formate la date pour le formulaire et renvoie toutes les données
        $task['due_date'] = $task['due_date']
            ? date('Y-m-d\TH:i', strtotime($task['due_date']))
            : null;

        Utility::jsonResponse([
            'success' => true,
            'data' => $task
        ]);
    }
} catch (Exception $e) {
    // En cas d'erreur, renvoie un message d'erreur au format JSON
    http_response_code(400);
    Utility::jsonResponse([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>