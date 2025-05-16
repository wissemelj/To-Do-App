<?php
/**
 * Fichier: task.php
 *
 * Page de détail d'une tâche spécifique dans l'application TacTâche.
 * Cette page affiche les informations complètes d'une tâche et permet
 * d'interagir avec celle-ci (commentaires, etc.).
 *
 * Fonctionnalités:
 * - Affichage des détails complets d'une tâche (titre, description, statut, échéance)
 * - Affichage des informations d'assignation et de création
 * - Affichage des commentaires associés à la tâche
 * - Formulaire pour ajouter un nouveau commentaire
 *
 * Paramètres:
 * - id: Identifiant de la tâche à afficher (passé via GET)
 *
 * Sécurité:
 * - Vérification que l'utilisateur est connecté
 * - Vérification que l'utilisateur a le droit de voir cette tâche
 * - Protection contre les injections SQL via requêtes préparées
 */
require_once '../src/includes/config.php';

// Vérifier que l'utilisateur est connecté
$userObj->requireLogin(SITE_URL . '/login.php');

// Récupération de l'ID de la tâche depuis l'URL
$taskId = $_GET['id'] ?? null;

// Redirection si aucun ID n'est fourni
if (!$taskId) {
    Utility::redirect('index.php');
    exit();
}

// Vérifier si l'utilisateur peut voir cette tâche
if (!$userObj->canViewTask((int)$taskId, $taskObj)) {
    Utility::redirect('index.php');
    exit();
}

// Récupérer les détails de la tâche avec les informations du créateur et de la personne assignée
$stmt = $pdo->prepare("
    SELECT tasks.*, users.username AS creator, assigned.username AS assignee
    FROM tasks
    LEFT JOIN users ON tasks.created_by = users.id
    LEFT JOIN users AS assigned ON tasks.assigned_to = assigned.id
    WHERE tasks.id = ?
");
$stmt->execute([$taskId]);
$task = $stmt->fetch();

// Redirection si la tâche n'existe pas
if (!$task) {
    header("Location: index.php");
    exit();
}

// Récupérer les commentaires associés à cette tâche
$stmt = $pdo->prepare("
    SELECT comments.*, users.username
    FROM comments
    JOIN users ON comments.user_id = users.id
    WHERE task_id = ?
");
$stmt->execute([$taskId]);
$comments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($task['title']) ?> - TacTâche</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .task-details {
            background: white;
            padding: 20px;
            border-radius: var(--border-radius-md);
            margin-bottom: 20px;
            box-shadow: var(--shadow-md);
        }

        .comments {
            background: white;
            padding: 20px;
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-md);
        }

        .comment {
            border-bottom: 1px solid var(--border-color);
            padding: 10px 0;
            margin-bottom: 10px;
        }

        .comments form {
            margin-top: 20px;
        }

        .comments textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-sm);
            margin-bottom: 10px;
            min-height: 80px;
            resize: vertical;
        }

        /* Comment button styles are now unified in styles.css */
        .comments button {
            min-width: 100px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php">← Retour</a>
        <h1><?= htmlspecialchars($task['title']) ?></h1>

        <div class="task-details">
            <p><strong>Description :</strong> <?= htmlspecialchars($task['description']) ?></p>
            <p><strong>Statut :</strong> <?= ucfirst($task['status']) ?></p>
            <p><strong>Échéance :</strong> <?= $task['due_date'] ? date('d/m/Y H:i', strtotime($task['due_date'])) : 'Non définie' ?></p>
            <p><strong>Assigné à :</strong> <?= $task['assignee'] ?? 'Personne' ?></p>
        </div>

        <!-- Commentaires -->
        <div class="comments">
            <h2>Commentaires</h2>
            <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    <p><strong><?= htmlspecialchars($comment['username']) ?></strong> (<?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?>)</p>
                    <p><?= htmlspecialchars($comment['content']) ?></p>
                </div>
            <?php endforeach; ?>

            <form action="../src/actions/comment_action.php" method="POST">
                <input type="hidden" name="task_id" value="<?= $taskId ?>">
                <textarea name="content" placeholder="Ajouter un commentaire..." required></textarea>
                <button type="submit" class="btn-primary btn-sm">Envoyer</button>
            </form>
        </div>
    </div>
</body>
</html>