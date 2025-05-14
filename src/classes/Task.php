<?php
/**
 * Classe Task
 *
 * Cette classe gère toutes les opérations liées aux tâches dans l'application :
 * - Création, lecture, mise à jour et suppression de tâches (CRUD)
 * - Organisation des tâches par statut
 * - Récupération des tâches pour l'affichage dans le calendrier
 * - Vérification de l'existence et de la propriété des tâches
 */
class Task {
    /**
     * Stocke l'objet PDO pour accéder à la base de données
     * Cette propriété est utilisée dans toutes les méthodes qui ont besoin
     * d'interagir avec la base de données.
     */
    private $pdo;

    /**
     * Constructeur de la classe Task
     *
     * Initialise l'objet Task avec une connexion à la base de données.
     *
     * @param PDO $pdo L'objet PDO représentant la connexion à la base de données
     */
    public function __construct(PDO $pdo) {
        // Stocke la connexion à la base de données pour une utilisation ultérieure
        $this->pdo = $pdo;
    }

    /**
     * Récupère toutes les tâches et les organise par statut
     *
     * Cette méthode récupère toutes les tâches de la base de données
     * et les organise dans un tableau associatif par statut (todo, in_progress, done).
     *
     * @return array Un tableau associatif avec les tâches organisées par statut
     */
    public function getTasksByStatus(): array {
        // Prépare et exécute la requête SQL pour récupérer toutes les tâches avec les noms d'utilisateurs
        $stmt = $this->pdo->prepare("
            SELECT tasks.*,
            users.username AS assigned_username,
            creator.username AS creator_username
            FROM tasks
            LEFT JOIN users ON tasks.assigned_to = users.id
            LEFT JOIN users AS creator ON tasks.created_by = creator.id
            ORDER BY tasks.due_date ASC
        ");

        $stmt->execute();

        // Récupère toutes les tâches
        $allTasks = $stmt->fetchAll();

        // Initialise un tableau pour organiser les tâches par statut
        $tasksByStatus = [
            'todo' => [],          // Tâches à faire
            'in_progress' => [],   // Tâches en cours
            'done' => []           // Tâches terminées
        ];

        // Parcourt toutes les tâches et les organise par statut
        foreach ($allTasks as $task) {
            $tasksByStatus[$task['status']][] = $task;
        }

        // Retourne le tableau organisé
        return $tasksByStatus;
    }

    /**
     * Récupère une tâche spécifique par son ID
     *
     * Cette méthode récupère les détails d'une tâche spécifique,
     * y compris les noms des utilisateurs associés.
     *
     * @param int $taskId L'ID de la tâche à récupérer
     * @return array|false Les données de la tâche ou false si non trouvée
     */
    public function getTask(int $taskId) {
        // Prépare et exécute la requête SQL avec un paramètre lié pour plus de sécurité
        $stmt = $this->pdo->prepare("
            SELECT tasks.*,
            users.username AS assigned_username,
            creator.username AS creator_username
            FROM tasks
            LEFT JOIN users ON tasks.assigned_to = users.id
            LEFT JOIN users AS creator ON tasks.created_by = creator.id
            WHERE tasks.id = ?
        ");

        $stmt->execute([$taskId]);

        // Retourne la tâche
        return $stmt->fetch();
    }

    /**
     * Crée une nouvelle tâche
     *
     * Cette méthode insère une nouvelle tâche dans la base de données
     * avec les données fournies.
     *
     * @param array $data Les données de la tâche à créer (title, description, user_id, etc.)
     * @return bool True si la création a réussi, false sinon
     */
    public function createTask(array $data): bool {
        try {
            // Récupère et prépare les données de la tâche
            $title = $data['title'];  // Titre de la tâche
            $description = $data['description'] ?? null;  // Description (peut être null)
            $userId = $data['user_id'];  // ID de l'utilisateur qui crée la tâche
            $assignedTo = $data['assigned_to'] ?: null;  // ID de l'utilisateur assigné (peut être null)
            $photoPath = $data['photo_path'] ?? null;  // Chemin de la photo (peut être null)

            // Formate la date d'échéance si elle existe
            $dueDate = $data['due_date'] ? date('Y-m-d H:i:s', strtotime($data['due_date'])) : null;

            // Prépare et exécute la requête SQL avec des paramètres liés pour plus de sécurité
            $stmt = $this->pdo->prepare("
                INSERT INTO tasks (title, description, created_by, assigned_to, due_date, photo_path, status)
                VALUES (?, ?, ?, ?, ?, ?, 'todo')
            ");

            $stmt->execute([$title, $description, $userId, $assignedTo, $dueDate, $photoPath]);

            // Retourne true pour indiquer que la création a réussi
            return true;
        } catch (PDOException $e) {
            // En cas d'erreur, retourne false
            return false;
        }
    }

    /**
     * Met à jour une tâche existante
     *
     * Cette méthode modifie une tâche existante dans la base de données
     * avec les nouvelles données fournies.
     *
     * @param array $data Les nouvelles données de la tâche (id, title, status, etc.)
     * @return bool True si la mise à jour a réussi, false sinon
     */
    public function updateTask(array $data): bool {
        try {
            // Récupère et prépare les données de la tâche
            $id = (int)$data['id'];  // ID de la tâche à mettre à jour
            $title = $data['title'];  // Nouveau titre
            $description = $data['description'] ?? null;  // Nouvelle description
            $status = $data['status'] ?? 'todo';  // Nouveau statut
            $photoPath = $data['photo_path'] ?? null;  // Chemin de la photo (peut être null)

            // Formate la date d'échéance si elle existe
            $dueDate = !empty($data['due_date']) ? date('Y-m-d H:i:s', strtotime($data['due_date'])) : null;

            $assignedTo = $data['assigned_to'] ?? null;  // Nouvel utilisateur assigné

            // Prépare et exécute la requête SQL avec des paramètres liés pour plus de sécurité
            $stmt = $this->pdo->prepare("
                UPDATE tasks SET
                title = ?,
                description = ?,
                status = ?,
                due_date = ?,
                assigned_to = ?,
                photo_path = ?
                WHERE id = ?
            ");

            $stmt->execute([$title, $description, $status, $dueDate, $assignedTo, $photoPath, $id]);

            // Retourne true pour indiquer que la mise à jour a réussi
            return true;
        } catch (PDOException $e) {
            // En cas d'erreur, retourne false
            return false;
        }
    }

    /**
     * Supprime une tâche
     *
     * Cette méthode supprime une tâche de la base de données et sa photo associée si elle existe.
     *
     * @param int $taskId L'ID de la tâche à supprimer
     * @return bool True si la suppression a réussi, false sinon
     */
    public function deleteTask(int $taskId): bool {
        try {
            // Récupère le chemin de la photo avant de supprimer la tâche
            $stmt = $this->pdo->prepare("SELECT photo_path FROM tasks WHERE id = ?");
            $stmt->execute([$taskId]);
            $photoPath = $stmt->fetchColumn();

            // Supprime la tâche de la base de données
            $stmt = $this->pdo->prepare("DELETE FROM tasks WHERE id = ?");
            $stmt->execute([$taskId]);

            // Si la tâche avait une photo, la supprimer du système de fichiers
            if (!empty($photoPath)) {
                self::deleteTaskPhoto($photoPath);
            }

            // Retourne true pour indiquer que la suppression a réussi
            return true;
        } catch (PDOException $e) {
            // En cas d'erreur, retourne false
            return false;
        }
    }

    /**
     * Récupère les tâches pour la vue calendrier
     *
     * Cette méthode récupère les tâches et les formate pour l'affichage
     * dans le calendrier FullCalendar.
     *
     * @return array Un tableau de tâches formatées pour le calendrier
     */
    public function getCalendarTasks(): array {
        // Prépare et exécute la requête SQL pour récupérer les tâches
        $stmt = $this->pdo->prepare("SELECT id, title, due_date AS start, status FROM tasks");
        $stmt->execute();
        $tasks = $stmt->fetchAll();

        // Initialise un tableau pour stocker les événements du calendrier
        $events = [];

        // Transforme chaque tâche en événement pour le calendrier
        foreach ($tasks as $task) {
            $events[] = [
                'id' => $task['id'],                // ID de la tâche
                'title' => $task['title'],          // Titre de la tâche
                'start' => $task['start'],          // Date de début (date d'échéance)
                'allDay' => true,                   // Événement toute la journée
                'extendedProps' => [
                    'status' => $task['status']     // Statut de la tâche pour le code couleur
                ]
            ];
        }

        // Retourne les événements formatés
        return $events;
    }

    /**
     * Vérifie si une tâche existe
     *
     * Cette méthode vérifie si une tâche avec l'ID spécifié existe dans la base de données.
     *
     * @param int $taskId L'ID de la tâche à vérifier
     * @return bool True si la tâche existe, false sinon
     */
    public function taskExists(int $taskId): bool {
        // Prépare et exécute la requête SQL avec un paramètre lié pour plus de sécurité
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM tasks WHERE id = ?");
        $stmt->execute([$taskId]);

        // Récupère le résultat et retourne true si le compte est supérieur à 0
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Récupère les informations de propriété d'une tâche
     *
     * Cette méthode récupère l'ID du créateur et de l'utilisateur assigné à une tâche.
     * Ces informations sont utilisées pour vérifier les permissions.
     *
     * @param int $taskId L'ID de la tâche
     * @return array|false Les informations de propriété ou false si non trouvée
     */
    public function getTaskOwnership(int $taskId) {
        // Prépare et exécute la requête SQL avec un paramètre lié pour plus de sécurité
        $stmt = $this->pdo->prepare("SELECT created_by, assigned_to FROM tasks WHERE id = ?");
        $stmt->execute([$taskId]);

        // Retourne les informations de propriété
        return $stmt->fetch();
    }

    /**
     * Vérifie si un utilisateur est le créateur d'une tâche
     *
     * Cette méthode vérifie si l'utilisateur spécifié est le créateur de la tâche.
     *
     * @param int $taskId L'ID de la tâche
     * @param int $userId L'ID de l'utilisateur
     * @return bool True si l'utilisateur est le créateur, false sinon
     */
    public function isTaskCreator(int $taskId, int $userId): bool {
        // Prépare et exécute la requête SQL avec des paramètres liés pour plus de sécurité
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM tasks WHERE id = ? AND created_by = ?");
        $stmt->execute([$taskId, $userId]);

        // Retourne true si l'utilisateur est le créateur
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Récupère les libellés des statuts de tâche
     *
     * Cette méthode statique retourne les libellés en français pour chaque statut.
     *
     * @return array Un tableau associatif des libellés de statut
     */
    public static function getStatusLabels(): array {
        // Retourne un tableau associatif des libellés de statut
        return [
            'todo' => 'À Faire',          // Tâches à faire
            'in_progress' => 'En Cours',  // Tâches en cours
            'done' => 'Terminé'           // Tâches terminées
        ];
    }

    /**
     * Gère le téléchargement d'une photo pour une tâche
     *
     * Cette méthode traite le fichier téléchargé, le déplace vers le dossier de destination
     * et retourne le chemin relatif du fichier.
     *
     * @param array $file Le fichier téléchargé ($_FILES['photo'])
     * @return string|null Le chemin relatif du fichier ou null en cas d'erreur
     */
    public static function handlePhotoUpload(array $file): ?string {
        // Vérifier si un fichier a été téléchargé
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // Vérifier le type de fichier (uniquement les images)
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return null;
        }

        // Créer un nom de fichier unique pour éviter les collisions
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $uniqueName = uniqid('task_', true) . '.' . $extension;

        // Définir le chemin de destination
        $uploadDir = 'uploads/tasks/';
        $uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/To-Do-App/public/' . $uploadDir;
        $filePath = $uploadPath . $uniqueName;

        // Déplacer le fichier téléchargé vers le dossier de destination
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Retourner le chemin relatif du fichier
            return $uploadDir . $uniqueName;
        }

        return null;
    }

    /**
     * Supprime la photo d'une tâche
     *
     * Cette méthode supprime le fichier photo associé à une tâche.
     *
     * @param string $photoPath Le chemin de la photo à supprimer
     * @return bool True si la suppression a réussi, false sinon
     */
    public static function deleteTaskPhoto(string $photoPath): bool {
        if (empty($photoPath)) {
            return false;
        }

        $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/To-Do-App/public/' . $photoPath;

        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }
}
