<?php
require_once '../src/includes/auth.php';
requireLogin();

require_once '../src/includes/database.php';

$taskId = $_GET['id'] ?? null;

if (!$taskId) {
    header("Location: index.php");
    exit();
}

// Vérifier si l'utilisateur peut voir cette tâche
if (!canViewTask((int)$taskId)) {
    header("Location: index.php");
    exit();
}

// Récupérer la tâche
$stmt = $pdo->prepare("
    SELECT tasks.*, users.username AS creator, assigned.username AS assignee
    FROM tasks
    LEFT JOIN users ON tasks.created_by = users.id
    LEFT JOIN users AS assigned ON tasks.assigned_to = assigned.id
    WHERE tasks.id = ?
");
$stmt->execute([$taskId]);
$task = $stmt->fetch();

if (!$task) {
    header("Location: index.php");
    exit();
}

// Récupérer les commentaires
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
    <title><?= htmlspecialchars($task['title']) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
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
                <button type="submit">Envoyer</button>
            </form>
        </div>
    </div>
</body>
</html>