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

    // Récupère les détails de la tâche
    $task = $taskObj->getTask($taskId);
    if (!$task) {
        return ['success' => false, 'error' => 'Tâche introuvable'];
    }

    // Vérifie si la tâche est terminée
    if ($task['status'] === 'done') {
        return [
            'success' => false,
            'error' => $errorPrefix . 'Les tâches terminées ne peuvent pas être modifiées'
        ];
    }

    // Vérifie si l'utilisateur a le droit de modifier cette tâche
    if (!$userObj->canModifyTask($taskId, $taskObj)) {
        return [
            'success' => false,
            'error' => 'Permission refusée: Seul le créateur, la personne assignée ou un manager peut modifier cette tâche'
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

    // Vérifie si la requête contient des données de formulaire multipart
    $isMultipart = isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') !== false;

    if ($isMultipart) {
        // Récupère les données du formulaire
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $assignedTo = $_POST['assigned_to'] ?? null;
        $dueDate = $_POST['due_date'] ?? null;

        // Traite le téléchargement de la photo si présent
        $photoPath = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $photoPath = Task::handlePhotoUpload($_FILES['photo']);
        }
    } else {
        // Récupère les données JSON envoyées dans le corps de la requête
        $data = json_decode(file_get_contents('php://input'), true);
        $title = trim($data['title'] ?? '');
        $description = trim($data['description'] ?? '');
        $assignedTo = $data['assigned_to'] ?? null;
        $dueDate = $data['due_date'] ?? null;
        $photoPath = null; // Pas de photo dans les requêtes JSON
    }

    // Valide que le titre est présent et non vide
    if (empty($title)) {
        Utility::jsonResponse(['success' => false, 'error' => 'Le titre est obligatoire']);
    }

    $userId = $userObj->getLoggedInUserId();

    // Vérifie les permissions d'assignation
    if ($assignedTo !== null && $assignedTo != $userId && !$userObj->isManager()) {
        Utility::jsonResponse([
            'success' => false,
            'error' => 'Permission refusée: Seul un manager peut assigner une tâche à un autre utilisateur'
        ]);
    }

    // Crée la tâche dans la base de données
    $result = $taskObj->createTask([
        'title' => $title,
        'description' => $description,
        'assigned_to' => $assignedTo,
        'due_date' => $dueDate,
        'photo_path' => $photoPath,
        'user_id' => $userId
    ]);

    // Renvoie le résultat
    Utility::jsonResponse(['success' => $result]);
}

/**
 * Gère la mise à jour d'une tâche existante
 */
function handleUpdateTask(): void {
    global $userObj, $taskObj, $pdo;

    // Vérifie si la requête contient des données de formulaire multipart
    $isMultipart = isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') !== false;

    if ($isMultipart) {
        // Récupère les données du formulaire
        $taskId = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'todo';
        $assignedTo = $_POST['assigned_to'] ?? null;
        $dueDate = $_POST['due_date'] ?? null;

        // Traite le téléchargement de la photo si présent
        $photoPath = null;
        $keepExistingPhoto = isset($_POST['keep_existing_photo']) && $_POST['keep_existing_photo'] === '1';

        if (!$keepExistingPhoto) {
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
                // Récupère l'ancienne photo pour la supprimer si nécessaire
                $stmt = $pdo->prepare("SELECT photo_path FROM tasks WHERE id = ?");
                $stmt->execute([$taskId]);
                $oldPhotoPath = $stmt->fetchColumn();

                // Télécharge la nouvelle photo
                $photoPath = Task::handlePhotoUpload($_FILES['photo']);

                // Supprime l'ancienne photo si elle existe
                if ($photoPath && !empty($oldPhotoPath)) {
                    Task::deleteTaskPhoto($oldPhotoPath);
                }
            } else {
                // Si aucune nouvelle photo n'est téléchargée et qu'on ne garde pas l'ancienne, on supprime l'ancienne
                $stmt = $pdo->prepare("SELECT photo_path FROM tasks WHERE id = ?");
                $stmt->execute([$taskId]);
                $oldPhotoPath = $stmt->fetchColumn();

                if (!empty($oldPhotoPath)) {
                    Task::deleteTaskPhoto($oldPhotoPath);
                }

                $photoPath = null;
            }
        } else {
            // Garde l'ancienne photo
            $stmt = $pdo->prepare("SELECT photo_path FROM tasks WHERE id = ?");
            $stmt->execute([$taskId]);
            $photoPath = $stmt->fetchColumn();
        }
    } else {
        // Récupère les données JSON envoyées dans le corps de la requête
        $input = json_decode(file_get_contents('php://input'), true);

        // Valide que les champs obligatoires sont présents et non vides
        $validation = Utility::validateRequired($input, ['id', 'title']);
        if (!$validation['valid']) {
            Utility::jsonResponse(['success' => false, 'error' => implode(', ', $validation['errors'])]);
        }

        $taskId = (int)$input['id'];
        $title = trim($input['title']);
        $description = trim($input['description'] ?? '');
        $status = $input['status'] ?? 'todo';
        $assignedTo = $input['assigned_to'] ?? null;
        $dueDate = $input['due_date'] ?? null;
        $photoPath = $input['photo_path'] ?? null;
    }

    // Vérifie si la tâche peut être modifiée
    $error = checkTaskModifiable($taskId);
    if ($error) {
        Utility::jsonResponse($error);
    }

    $userId = $userObj->getLoggedInUserId();

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
        'title' => $title,
        'description' => $description,
        'status' => $status,
        'due_date' => $dueDate,
        'assigned_to' => $assignedTo,
        'photo_path' => $photoPath
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
