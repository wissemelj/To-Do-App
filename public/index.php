<?php
require_once '../src/includes/auth.php';
requireLogin();

require_once '../src/includes/database.php';

$userId = getLoggedInUserId();

// Récupération des tâches
$stmt = $pdo->prepare("
    SELECT tasks.*, users.username AS assigned_username 
    FROM tasks 
    LEFT JOIN users ON tasks.assigned_to = users.id 
    WHERE tasks.created_by = ? OR tasks.assigned_to = ?
    ORDER BY tasks.due_date ASC
");
$stmt->execute([$userId, $userId]);
$allTasks = $stmt->fetchAll();

// Organisation par statut
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
    <meta charset="UTF-8">
    <title>Tableau de bord - Gestion des tâches</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body>
    <div class="container">
        <header>
            <h1>
                Mes Tâches
                <button onclick="showTaskForm()">+ Nouvelle tâche</button>
                <a href="logout.php" class="logout-btn">Déconnexion</a>
            </h1>
        </header>

        <div class="board">
            <?php foreach (['todo' => 'À Faire', 'in_progress' => 'En Cours', 'done' => 'Terminé'] as $status => $label): ?>
            <div class="column" data-status="<?= $status ?>">
                <h2><?= $label ?></h2>
                <div class="task-list">
                    <?php foreach ($tasksByStatus[$status] as $task): ?>
                    <div class="task" data-task-id="<?= $task['id'] ?>">
                        <div class="task-header">
                            <h3>
                                <a href="task.php?id=<?= $task['id'] ?>">
                                    <?= htmlspecialchars($task['title']) ?>
                                </a>
                            </h3>
                            <div class="task-actions">
                                <button onclick="editTask(<?= $task['id'] ?>)">✏️</button>
                                <button onclick="deleteTask(<?= $task['id'] ?>)">🗑️</button>
                            </div>
                        </div>
                        <?php if (!empty($task['description'])): ?>
                        <p><?= htmlspecialchars($task['description']) ?></p>
                        <?php endif; ?>
                        <?php if ($task['assigned_username']): ?>
                        <div class="assigned-to">
                            👤 <?= htmlspecialchars($task['assigned_username']) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Formulaire de création -->
        <div id="taskForm" class="modal-overlay" style="display: none;">
            <div class="modal-content">
                <h3>Nouvelle tâche</h3>
                <form id="newTaskForm">
                    <div class="form-group">
                        <input type="text" name="title" placeholder="Titre" required>
                    </div>
                    
                    <div class="form-group">
                        <textarea name="description" placeholder="Description"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Assigner à :</label>
                        <select name="assigned_to" id="assignedToSelect">
                            <option value="">Personne</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Date limite :</label>
                        <input type="datetime-local" name="due_date">
                    </div>

                    <div class="form-buttons">
                        <button type="submit">Créer</button>
                        <button type="button" onclick="hideTaskForm()">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    // État global
    let users = [];

    // Chargement initial
    document.addEventListener('DOMContentLoaded', async () => {
        try {
            // Charger les utilisateurs
            const usersResponse = await axios.get('../src/actions/get_users.php');
            if (usersResponse.data.success) {
                users = usersResponse.data.data;
                updateAssignSelect();
            }

            // Initialiser le drag and drop
            initSortable();
        } catch (error) {
            console.error('Erreur initialisation:', error);
        }
    });

    // Mettre à jour la liste déroulante d'assignation
    function updateAssignSelect() {
        const select = document.getElementById('assignedToSelect');
        select.innerHTML = `
            <option value="">Personne</option>
            ${users.map(user => `
                <option value="${user.id}">${user.username}</option>
            `).join('')}
        `;
    }

    // Gestion formulaires
    function showTaskForm() {
        document.getElementById('taskForm').style.display = 'flex';
    }

    function hideTaskForm() {
        document.getElementById('taskForm').style.display = 'none';
    }

    // Soumission formulaire création
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
            console.error('Erreur complète:', error);
            alert('Erreur réseau: ' + error.message);
        }
    });

    // Drag and drop
    function initSortable() {
        document.querySelectorAll('.task-list').forEach(list => {
            Sortable.create(list, {
                group: 'tasks',
                animation: 150,
                onEnd: async (e) => {
                    const taskId = e.item.dataset.taskId;
                    const newStatus = e.to.parentElement.dataset.status;
                    
                    try {
                        await axios.post('../src/actions/update_task_status.php', {
                            task_id: taskId,
                            status: newStatus
                        });
                    } catch (error) {
                        alert('Erreur mise à jour statut: ' + error.response?.data?.error);
                    }
                }
            });
        });
    }

    // Suppression
    async function deleteTask(taskId) {
        if (!confirm('Supprimer cette tâche définitivement ?')) return;
        
        try {
            await axios.post('../src/actions/delete_task.php', { task_id: taskId });
            window.location.reload();
        } catch (error) {
            alert('Erreur suppression: ' + error.response?.data?.error);
        }
    }

    // Édition
    async function editTask(taskId) {
        try {
            const response = await axios.get(`../src/actions/get_task.php?id=${taskId}`);
            if (!response.data.success) throw new Error(response.data.error);
            showEditForm(response.data.data);
        } catch (error) {
            alert('Erreur: ' + error.message);
        }
    }

    // Remplacer la fonction showEditForm par :
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
                                <option value="${user.id}" ${task.assigned_to == user.id ? 'selected' : ''}>
                                    ${user.username}
                                </option>
                            `).join('')}
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Date limite:</label>
                        <input type="datetime-local" name="due_date" 
                            value="${task.due_date ? task.due_date.slice(0, 16) : ''}">
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
                headers: {
                    'Content-Type': 'application/json'
                }
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