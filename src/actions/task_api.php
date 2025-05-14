<?php
/**
 * Fichier: task_api.php
 *
 * Ce fichier centralise toutes les opérations CRUD (Create, Read, Update, Delete)
 * liées aux tâches dans l'application TacTâche.
 *
 * Il gère les requêtes suivantes :
 * - GET avec 'action=get' : Récupérer les détails d'une tâche
 * - GET avec 'action=calendar' : Récupérer les tâches pour le calendrier
 * - POST avec 'action=create' : Créer une nouvelle tâche
 * - POST avec 'action=update' : Mettre à jour une tâche existante
 * - POST avec 'action=delete' : Supprimer une tâche
 * - POST avec 'action=update_status' : Mettre à jour le statut d'une tâche
 *
 * Méthodes HTTP: GET, POST
 * Format de données: JSON
 * Réponse: JSON
 */

// Inclusion du fichier de configuration qui charge les classes et initialise les objets
require_once __DIR__ . '/../includes/config.php';

// Définit le type de contenu de la réponse comme JSON
header('Content-Type: application/json');

// Vérifie si l'utilisateur est connecté
if (!$userObj->isLoggedIn()) {
    Utility::jsonResponse(['success' => false, 'error' => 'Non authentifié']);
}

// Détermine l'action à effectuer en fonction de la méthode HTTP et des paramètres
$method = $_SERVER['REQUEST_METHOD'];
$action = '';

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';
} elseif ($method === 'POST') {
    $action = $_POST['action'] ?? '';

    // Si l'action n'est pas dans $_POST, essaie de la récupérer depuis les données JSON
    if (empty($action)) {
        $jsonData = json_decode(file_get_contents('php://input'), true);
        $action = $jsonData['action'] ?? '';
    }
}

/**
 * Vérifie si une tâche peut être modifiée
 *
 * @param int $taskId ID de la tâche à vérifier
 * @param bool $checkExists Vérifier si la tâche existe
 * @param string $errorPrefix Préfixe pour le message d'erreur
 * @return array|null Tableau d'erreur ou null si la tâche peut être modifiée
 */
function checkTaskModifiable(int $taskId, bool $checkExists = true, string $errorPrefix = ''): ?array {
    global $userObj, $taskObj;

    // Vérifie si la tâche existe
    if ($checkExists && !$taskObj->taskExists($taskId)) {
        return ['success' => false, 'error' => 'Tâche introuvable'];
    }

    // Vérifie si l'utilisateur a le droit de modifier cette tâche
    if (!$userObj->canModifyTask($taskId, $taskObj)) {
        return [
            'success' => false,
            'error' => $errorPrefix . 'Les tâches terminées ne peuvent pas être modifiées'
        ];
    }

    return null;
}

// Traite la requête en fonction de l'action
try {
    switch ($action) {
        // Récupérer les détails d'une tâche
        case 'get':
            handleGetTask();
            break;

        // Récupérer les tâches pour le calendrier
        case 'calendar':
            handleGetCalendarTasks();
            break;

        // Créer une nouvelle tâche
        case 'create':
            handleCreateTask();
            break;

        // Mettre à jour une tâche existante
        case 'update':
            handleUpdateTask();
            break;

        // Supprimer une tâche
        case 'delete':
            handleDeleteTask();
            break;

        // Mettre à jour le statut d'une tâche
        case 'update_status':
            handleUpdateTaskStatus();
            break;

        // Action non reconnue
        default:
            throw new Exception('Action non reconnue ou manquante');
    }
} catch (Exception $e) {
    // En cas d'erreur, renvoie un message d'erreur au format JSON
    http_response_code(400);
    Utility::jsonResponse([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Gère la récupération des détails d'une tâche
 */
function handleGetTask(): void {
    global $userObj, $taskObj;

    // Récupère l'ID de la tâche et le mode depuis les paramètres GET
    $taskId = $_GET['id'] ?? null;
    $mode = $_GET['mode'] ?? 'edit';

    // Vérifie que l'ID de la tâche est présent et numérique
    if (!$taskId || !is_numeric($taskId)) {
        throw new Exception('ID de tâche invalide ou manquant');
    }

    $taskId = (int)$taskId;

    // Vérifie si l'utilisateur a le droit de voir cette tâche
    if (!$userObj->canViewTask($taskId, $taskObj)) {
        throw new Exception('Tâche introuvable ou accès non autorisé');
    }

    // Récupère les détails de la tâche
    $task = $taskObj->getTask($taskId);
    if (!$task) {
        throw new Exception('Tâche introuvable');
    }

    // Formate la date pour le mode édition
    if ($mode !== 'calendar' && $task['due_date']) {
        $task['due_date'] = date('Y-m-d\TH:i', strtotime($task['due_date']));
    }

    // Renvoie les données avec la clé appropriée selon le mode
    Utility::jsonResponse([
        'success' => true,
        $mode === 'calendar' ? 'task' : 'data' => $task
    ]);
}

/**
 * Gère la récupération des tâches pour le calendrier
 */
function handleGetCalendarTasks(): void {
    global $taskObj;

    // Récupère les tâches formatées pour le calendrier et les renvoie au format JSON
    header('Content-Type: application/json');
    echo json_encode($taskObj->getCalendarTasks());
    exit();
}

/**
 * Gère la création d'une nouvelle tâche
 */
function handleCreateTask(): void {
    global $userObj, $taskObj;

    // Récupère les données JSON envoyées dans le corps de la requête
    $data = json_decode(file_get_contents('php://input'), true);

    // Valide que les champs obligatoires sont présents et non vides
    $validation = Utility::validateRequired($data, ['title']);
    if (!$validation['valid']) {
        Utility::jsonResponse(['success' => false, 'error' => 'Le titre est obligatoire']);
    }

    $userId = $userObj->getLoggedInUserId();
    $assignedTo = $data['assigned_to'] ?? null;

    // Vérifie les permissions d'assignation
    if ($assignedTo !== null && $assignedTo != $userId && !$userObj->isManager()) {
        Utility::jsonResponse([
            'success' => false,
            'error' => 'Permission refusée: Seul un manager peut assigner une tâche à un autre utilisateur'
        ]);
    }

    // Crée la tâche dans la base de données
    $result = $taskObj->createTask([
        'title' => trim($data['title']),
        'description' => trim($data['description'] ?? ''),
        'assigned_to' => $assignedTo,
        'due_date' => $data['due_date'] ?? null,
        'user_id' => $userId
    ]);

    // Renvoie le résultat
    Utility::jsonResponse(['success' => $result]);
}

/**
 * Gère la mise à jour d'une tâche existante
 */
function handleUpdateTask(): void {
    global $userObj, $taskObj;

    // Récupère les données JSON envoyées dans le corps de la requête
    $input = json_decode(file_get_contents('php://input'), true);

    // Valide que les champs obligatoires sont présents et non vides
    $validation = Utility::validateRequired($input, ['id', 'title']);
    if (!$validation['valid']) {
        Utility::jsonResponse(['success' => false, 'error' => implode(', ', $validation['errors'])]);
    }

    $taskId = (int)$input['id'];

    // Vérifie si la tâche peut être modifiée
    $error = checkTaskModifiable($taskId);
    if ($error) {
        Utility::jsonResponse($error);
    }

    $userId = $userObj->getLoggedInUserId();
    $assignedTo = $input['assigned_to'] ?? null;

    // Vérifie les permissions d'assignation
    if ($assignedTo !== null && $assignedTo != $userId && !$userObj->isManager()) {
        Utility::jsonResponse([
            'success' => false,
            'error' => 'Permission refusée: Seul un manager peut assigner une tâche à un autre utilisateur'
        ]);
    }

    // Met à jour la tâche dans la base de données
    $result = $taskObj->updateTask([
        'id' => $taskId,
        'title' => $input['title'],
        'description' => $input['description'] ?? null,
        'status' => $input['status'] ?? 'todo',
        'due_date' => $input['due_date'] ?? null,
        'assigned_to' => $assignedTo
    ]);

    // Renvoie le résultat
    Utility::jsonResponse(['success' => $result]);
}

/**
 * Gère la suppression d'une tâche
 */
function handleDeleteTask(): void {
    global $userObj, $taskObj;

    // Récupère les données JSON envoyées dans le corps de la requête
    $data = json_decode(file_get_contents('php://input'), true);
    $taskId = $data['task_id'] ?? null;

    // Vérifie que l'ID de la tâche est présent
    if (!$taskId) {
        Utility::jsonResponse(['success' => false, 'error' => 'ID manquant']);
    }

    $taskId = (int)$taskId;

    // Vérifie si la tâche existe
    if (!$taskObj->taskExists($taskId)) {
        Utility::jsonResponse(['success' => false, 'error' => 'Tâche introuvable']);
    }

    // Pour la suppression, on ne vérifie pas si la tâche est terminée
    // car les tâches terminées peuvent être supprimées
    if (!$userObj->isManager() && !$taskObj->isTaskCreator($taskId, $userObj->getLoggedInUserId())) {
        Utility::jsonResponse([
            'success' => false,
            'error' => 'Permission refusée: Seul le créateur ou un manager peut supprimer cette tâche'
        ]);
    }

    // Supprime la tâche et renvoie le résultat
    Utility::jsonResponse(['success' => $taskObj->deleteTask($taskId)]);
}

/**
 * Gère la mise à jour du statut d'une tâche
 */
function handleUpdateTaskStatus(): void {
    global $pdo, $userObj, $taskObj;

    // Récupère les données JSON envoyées dans le corps de la requête
    $data = json_decode(file_get_contents('php://input'), true);
    $taskId = $data['task_id'] ?? null;

    // Vérifie que l'ID de la tâche est présent
    if (!$taskId) {
        Utility::jsonResponse(['success' => false, 'error' => 'ID manquant']);
    }

    $taskId = (int)$taskId;

    // Vérifie si l'utilisateur a le droit de modifier cette tâche
    if (!$userObj->canModifyTask($taskId, $taskObj)) {
        Utility::jsonResponse([
            'success' => false,
            'error' => 'Permission refusée: Seul le créateur, la personne assignée ou un manager peut modifier cette tâche'
        ]);
    }

    try {
        // Exécute la requête pour mettre à jour la date d'échéance
        $stmt = $pdo->prepare("UPDATE tasks SET due_date = ? WHERE id = ?");
        $stmt->execute([
            date('Y-m-d H:i:s', strtotime($data['new_date'])),
            $taskId
        ]);

        Utility::jsonResponse(['success' => true]);
    } catch (PDOException) {
        Utility::jsonResponse(['success' => false, 'error' => 'Erreur lors de la mise à jour']);
    }
}
