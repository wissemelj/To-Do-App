<?php
require_once '../src/includes/auth.php';
require_once '../src/includes/database.php';
require_once '../src/includes/utils.php';

requireLogin();
$userId = getLoggedInUserId();
ensureUsernameInSession($pdo, $userId);
$tasksByStatus = getTasksByStatus($pdo);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Task Manager</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>
    <div class="container">
        <header class="dashboard-header">
            <div class="header-left">
                <h1 class="header-title">Tâches</h1>
                <span class="user-role"><?= isManager() ? 'Manager' : 'Collaborateur' ?></span>
            </div>
            <div>
                <button class="btn-primary" onclick="showTaskForm()">+ Nouvelle tâche</button>
                <a href="calendar.php" class="btn-primary">Calendrier</a>
                <a href="logout.php" class="btn-primary" style="margin-left: 10px;">Déconnexion</a>
            </div>
        </header>

        <div class="board">
            <?php foreach (getStatusLabels() as $status => $label): ?>
            <div class="column">
                <h2 class="column-header"><?= $label ?></h2>
                <div class="task-list">
                    <?php foreach ($tasksByStatus[$status] as $task): ?>
                    <div class="task" data-task-id="<?= $task['id'] ?>">
                        <div class="task-header">
                            <h3 class="task-title"><?= h($task['title']) ?></h3>
                            <div class="task-actions">
                                <?php if (isManager() || $task['created_by'] === $userId || $task['assigned_to'] === $userId): ?>
                                    <button onclick="editTask(<?= $task['id'] ?>)" title="Modifier">✏️</button>
                                    <?php if (isManager() || $task['created_by'] === $userId): ?>
                                        <button onclick="deleteTask(<?= $task['id'] ?>)" title="Supprimer">🗑️</button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (!empty($task['description'])): ?>
                        <p class="task-description"><?= h($task['description']) ?></p>
                        <?php endif; ?>
                        <div class="task-footer">
                            <?php if ($task['due_date']): ?>
                            <span class="task-due">📅 <?= formatDate($task['due_date']) ?></span>
                            <?php endif; ?>
                            <?php if ($task['assigned_username']): ?>
                            <span class="task-assignee">👤 <?= h($task['assigned_username']) ?></span>
                            <?php endif; ?>
                            <?php if (isset($task['creator_username'])): ?>
                            <span class="task-creator">📝 <?= h($task['creator_username']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div id="taskForm" class="modal-overlay" style="display: none;">
            <div class="modal-content">
                <h3>Créer une nouvelle tâche</h3>
                <button type="button" class="modal-close" onclick="hideTaskForm()">&times;</button>
                <form id="newTaskForm">
                    <div class="form-group">
                        <label for="task-title">Titre:</label>
                        <input type="text" id="task-title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="task-description">Description:</label>
                        <textarea id="task-description" name="description"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="task-assigned">Assigner à:</label>
                        <?php if (isCollaborator()): ?>
                            <input type="hidden" name="assigned_to" value="<?= getLoggedInUserId() ?>">
                            <select id="task-assigned" disabled>
                                <option value="<?= getLoggedInUserId() ?>"><?= h($_SESSION['username'] ?? 'Vous-même') ?></option>
                            </select>
                            <small class="form-hint">En tant que collaborateur, vous ne pouvez créer des tâches que pour vous-même.</small>
                        <?php else: ?>
                            <select id="task-assigned" name="assigned_to">
                                <option value="">Personne</option>
                                <?php
                                $users = $pdo->query("SELECT id, username FROM users")->fetchAll();
                                foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>"><?= h($user['username']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="task-due-date">Date limite:</label>
                        <input type="datetime-local" id="task-due-date" name="due_date">
                    </div>
                    <div class="form-buttons">
                        <button type="submit" class="btn-primary">Créer</button>
                        <button type="button" class="btn-secondary" onclick="hideTaskForm()">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="assets/js/common.js"></script>

    <script>
    let users = [];

    document.addEventListener('DOMContentLoaded', async () => {
        try {
            const response = await axios.get(API_PATHS.GET_USERS);
            if (response.data.success) {
                users = response.data.data;
            }
        } catch (error) {
            console.error('Erreur initialisation:', error);
        }
    });

    function showTaskForm() {
        toggleModal('taskForm', true);
    }

    function hideTaskForm() {
        toggleModal('taskForm', false);
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
            const response = await axios.post(API_PATHS.CREATE_TASK, formData);
            if (response.data.success) {
                window.location.reload();
            } else {
                alert('Erreur: ' + (response.data.error || 'Inconnue'));
            }
        } catch (error) {
            handleApiError(error, 'Erreur lors de la création de la tâche');
        }
    });

    async function deleteTask(taskId) {
        if (!confirm('Supprimer cette tâche définitivement ?')) return;

        try {
            const response = await axios.post(API_PATHS.DELETE_TASK, { task_id: taskId });
            if (response.data.success) {
                window.location.reload();
            } else {
                handleApiError({ response: { data: response.data } }, 'Suppression échouée');
            }
        } catch (error) {
            handleApiError(error, 'Erreur lors de la suppression');
        }
    }

    async function editTask(taskId) {
        try {
            const response = await axios.get(`${API_PATHS.GET_TASK}?id=${taskId}`);
            if (!response.data.success) {
                throw new Error(response.data.error);
            }
            showEditForm(response.data.data);
        } catch (error) {
            handleApiError(error, 'Impossible de récupérer les détails de la tâche');
        }
    }

    function showEditForm(task) {
        const isCollaborator = <?= isCollaborator() ? 'true' : 'false' ?>;
        const currentUserId = <?= getLoggedInUserId() ?>;
        const currentUsername = '<?= h($_SESSION['username'] ?? 'Vous-même') ?>';

        const assigneeOptions = isCollaborator
            ? `<input type="hidden" name="assigned_to" value="${currentUserId}">
               <select id="edit-task-assigned" disabled>
                 <option value="${currentUserId}" selected>${currentUsername}</option>
               </select>
               <small class="form-hint">En tant que collaborateur, vous ne pouvez assigner des tâches qu'à vous-même.</small>`
            : `<select id="edit-task-assigned" name="assigned_to">
                 <option value="">Personne</option>
                 ${users.map(user => `<option value="${user.id}" ${task.assigned_to == user.id ? 'selected' : ''}>${user.username}</option>`).join('')}
               </select>`;

        const formHTML = `
        <div class="modal-overlay" id="editModal">
            <div class="modal-content">
                <h3>Modifier la tâche</h3>
                <button type="button" class="modal-close" onclick="closeEditForm()">&times;</button>
                <form id="editForm">
                    <input type="hidden" name="id" value="${task.id}">
                    <div class="form-group">
                        <label for="edit-task-title">Titre:</label>
                        <input type="text" id="edit-task-title" name="title" value="${task.title}" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-task-description">Description:</label>
                        <textarea id="edit-task-description" name="description">${task.description || ''}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit-task-status">Statut:</label>
                        <select id="edit-task-status" name="status">
                            ${Object.entries(STATUS_LABELS).map(([value, label]) =>
                                `<option value="${value}" ${task.status === value ? 'selected' : ''}>${label}</option>`
                            ).join('')}
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit-task-assigned">Assigné à:</label>
                        ${assigneeOptions}
                    </div>
                    <div class="form-group">
                        <label for="edit-task-due-date">Date limite:</label>
                        <input type="datetime-local" id="edit-task-due-date" name="due_date" value="${task.due_date ? task.due_date.slice(0, 16) : ''}">
                    </div>
                    <div class="form-buttons">
                        <button type="submit" class="btn-primary">Enregistrer</button>
                        <button type="button" class="btn-secondary" onclick="closeEditForm()">Annuler</button>
                    </div>
                </form>
            </div>
        </div>`;

        document.body.insertAdjacentHTML('beforeend', formHTML);
        document.getElementById('editForm').addEventListener('submit', submitEditForm);
    }

    async function submitEditForm(e) {
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
            const response = await axios.post(API_PATHS.EDIT_TASK, formData, {
                headers: { 'Content-Type': 'application/json' }
            });

            if (response.data.success) {
                window.location.reload();
            } else {
                handleApiError({ response: { data: response.data } }, 'Modification échouée');
            }
        } catch (error) {
            handleApiError(error, 'Erreur lors de la modification');
        }
    }

    function closeEditForm() {
        document.getElementById('editModal').remove();
    }
    </script>
</body>
</html>
