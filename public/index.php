<?php
// --- Authentification ---
require_once '../src/includes/auth.php';
requireLogin();

// --- Connexion à la base de données ---
require_once '../src/includes/database.php';
$userId = getLoggedInUserId();

// --- Récupération des tâches (créées ou assignées à l'utilisateur) ---
$stmt = $pdo->prepare("
    SELECT tasks.*, users.username AS assigned_username 
    FROM tasks 
    LEFT JOIN users ON tasks.assigned_to = users.id 
    WHERE tasks.created_by = ? OR tasks.assigned_to = ?
    ORDER BY tasks.due_date ASC
");
$stmt->execute([$userId, $userId]);
$allTasks = $stmt->fetchAll();

// --- Organisation des tâches par statut ---
$tasksByStatus = [
    'todo' => [],
    'in_progress' => [],
    'done' => []
];
foreach ($allTasks as $task) {
    $tasksByStatus[$task['status']][] = $task;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tableau de bord - Task Manager</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/index.css">

</head>
<body>
    <div class="container">
        <!-- En-tête -->
        <header class="dashboard-header">
            <h1 class="header-title"> Tâches</h1>
            <div>
                <button class="btn-primary" onclick="showTaskForm()">+ Nouvelle tâche</button>
                <a href="calendar.php" class="btn-primary" >Calendrier</a>
                <a href="logout.php" class="btn-primary" style="margin-left: 10px;">Déconnexion</a>
            </div>
        </header>

        <!-- Tableau des tâches -->
        <div class="board">
            <?php foreach (['todo' => 'À Faire', 'in_progress' => 'En Cours', 'done' => 'Terminé'] as $status => $label): ?>
            <div class="column">
                <h2 class="column-header"><?= $label ?></h2>
                <div class="task-list">
                    <?php foreach ($tasksByStatus[$status] as $task): ?>
                    <div class="task" data-task-id="<?= $task['id'] ?>">
                        <div class="task-header">
                            <h3 class="task-title"><?= htmlspecialchars($task['title']) ?></h3>
                            <div class="task-actions">
                                <button onclick="editTask(<?= $task['id'] ?>)" title="Modifier">✏️</button>
                                <button onclick="deleteTask(<?= $task['id'] ?>)" title="Supprimer">🗑️</button>
                            </div>
                        </div>
                        <?php if (!empty($task['description'])): ?>
                        <p class="task-description"><?= htmlspecialchars($task['description']) ?></p>
                        <?php endif; ?>
                        <div class="task-footer">
                            <?php if ($task['due_date']): ?>
                            <span class="task-due">📅 <?= date('d/m/Y H:i', strtotime($task['due_date'])) ?></span>
                            <?php endif; ?>
                            <?php if ($task['assigned_username']): ?>
                            <span class="task-assignee">👤 <?= htmlspecialchars($task['assigned_username']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Formulaire de création -->
        <div id="taskForm" class="modal-overlay" style="display: none;">
            <div class="modal-content">
                <h3>Créer une nouvelle tâche</h3>
                <form id="newTaskForm">
                    <div class="form-group">
                        <input type="text" name="title" placeholder="Titre" required>
                    </div>
                    <div class="form-group">
                        <textarea name="description" placeholder="Description"></textarea>
                    </div>
                    <div class="form-group">
                        <select name="assigned_to">
                            <option value="">Assigner à...</option>
                            <?php 
                            $users = $pdo->query("SELECT id, username FROM users")->fetchAll();
                            foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="datetime-local" name="due_date">
                    </div>
                    <div class="form-buttons">
                        <button type="submit" class="btn-primary">Créer</button>
                        <button type="button" class="btn-primary" onclick="hideTaskForm()">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JS externe -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script>
    // État global
    let users = [];

    // Initialisation
    document.addEventListener('DOMContentLoaded', async () => {
        try {
            const usersResponse = await axios.get('../src/actions/get_users.php');
            if (usersResponse.data.success) {
                users = usersResponse.data.data;
                updateAssignSelect();
            }
        } catch (error) {
            console.error('Erreur initialisation:', error);
        }
    });

    function updateAssignSelect() {
        const select = document.getElementById('assignedToSelect');
        if (!select) return;
        select.innerHTML = `<option value="">Personne</option>` +
            users.map(user => `<option value="${user.id}">${user.username}</option>`).join('');
    }

    function showTaskForm() {
        document.getElementById('taskForm').style.display = 'flex';
    }

    function hideTaskForm() {
        document.getElementById('taskForm').style.display = 'none';
    }

    document.getElementById('newTaskForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = {
            title: e.target.title.value,
            description: e.target.description.value,
            assigned_to: e.target.assigned_to.value || null,
            due_date: e.target.due_date.value
        };
        try {
            const response = await axios.post('../src/actions/task_action.php', formData);
            if (response.data.success) {
                window.location.reload();
            } else {
                alert('Erreur: ' + (response.data.error || 'Inconnue'));
            }
        } catch (error) {
            alert('Erreur réseau: ' + error.message);
        }
    });

    async function deleteTask(taskId) {
        if (!confirm('Supprimer cette tâche définitivement ?')) return;
        try {
            await axios.post('../src/actions/delete_task.php', { task_id: taskId });
            window.location.reload();
        } catch (error) {
            alert('Erreur suppression: ' + error.response?.data?.error);
        }
    }

    async function editTask(taskId) {
        try {
            const response = await axios.get(`../src/actions/get_task.php?id=${taskId}`);
            if (!response.data.success) throw new Error(response.data.error);
            showEditForm(response.data.data);
        } catch (error) {
            alert('Erreur: ' + error.message);
        }
    }

    function showEditForm(task) {
        const formHTML = `
        <div class="modal-overlay" id="editModal">
            <div class="modal-content">
                <h3>Modifier la tâche</h3>
                <form id="editForm">
                    <input type="hidden" name="id" value="${task.id}">
                    <div class="form-group">
                        <label>Titre:</label>
                        <input type="text" name="title" value="${task.title}" required>
                    </div>
                    <div class="form-group">
                        <label>Description:</label>
                        <textarea name="description">${task.description || ''}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Statut:</label>
                        <select name="status">
                            <option value="todo" ${task.status === 'todo' ? 'selected' : ''}>À faire</option>
                            <option value="in_progress" ${task.status === 'in_progress' ? 'selected' : ''}>En cours</option>
                            <option value="done" ${task.status === 'done' ? 'selected' : ''}>Terminé</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Assigné à:</label>
                        <select name="assigned_to">
                            <option value="">Personne</option>
                            ${users.map(user => `
                                <option value="${user.id}" ${task.assigned_to == user.id ? 'selected' : ''}>${user.username}</option>
                            `).join('')}
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date limite:</label>
                        <input type="datetime-local" name="due_date" value="${task.due_date ? task.due_date.slice(0, 16) : ''}">
                    </div>
                    <div class="form-buttons">
                        <button type="submit">Enregistrer</button>
                        <button type="button" onclick="closeEditForm()">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
        `;
        document.body.insertAdjacentHTML('beforeend', formHTML);
        document.getElementById('editForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = {
                id: e.target.id.value,
                title: e.target.title.value,
                description: e.target.description.value,
                status: e.target.status.value,
                assigned_to: e.target.assigned_to.value || null,
                due_date: e.target.due_date.value
            };
            try {
                const response = await axios.post('../src/actions/edit_task.php', formData, {
                    headers: { 'Content-Type': 'application/json' }
                });
                if (response.data.success) {
                    window.location.reload();
                } else {
                    alert('Erreur : ' + (response.data.error || 'Modification échouée'));
                }
            } catch (error) {
                alert('Erreur réseau : ' + error.message);
            }
        });
    }

    function closeEditForm() {
        document.getElementById('editModal').remove();
    }
    </script>
</body>
</html>
