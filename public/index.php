<?php
/**
 * Fichier: index.php
 *
 * Page principale de l'application TacTâche qui affiche le tableau de bord des tâches.
 * Cette page présente les tâches organisées par statut (À Faire, En Cours, Terminé)
 * dans une interface de type Kanban.
 *
 * Fonctionnalités:
 * - Affichage des tâches par statut
 * - Création de nouvelles tâches
 * - Modification des tâches existantes
 * - Suppression des tâches
 * - Interface différente selon le rôle de l'utilisateur (manager/collaborateur)
 *
 * Sécurité:
 * - Accès restreint aux utilisateurs authentifiés
 * - Permissions différentes selon le rôle de l'utilisateur
 */

// Inclusion du fichier de configuration qui charge les classes et initialise les objets
require_once '../src/includes/config.php';

// Vérifier que l'utilisateur est connecté (sinon redirection vers login.php)
$userObj->requireLogin(SITE_URL . '/login.php');

// S'assurer que le nom d'utilisateur est dans la session
$userObj->ensureUsernameInSession();

// Récupérer toutes les tâches et les organiser par statut (todo, in_progress, done)
$tasksByStatus = $taskObj->getTasksByStatus();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - TacTâche</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <script>
        // Afficher le rôle de l'utilisateur dans la console pour débogage
        console.log('Rôle utilisateur: <?= $userObj->getUserRole() ?>');
        console.log('Est collaborateur: <?= $userObj->isCollaborator() ? 'Oui' : 'Non' ?>');
        console.log('Est manager: <?= $userObj->isManager() ? 'Oui' : 'Non' ?>');
    </script>
</head>
<body>
    <div class="container">
        <header class="dashboard-header">
            <div class="header-left">
                <h1 class="header-title">TacTâche - Backlog</h1>
                <span class="user-role"><?= $userObj->isManager() ? 'Manager' : 'Collaborateur' ?></span>
            </div>
            <div>
                <button class="btn-primary" onclick="showTaskForm()">+ Nouvelle tâche</button>
                <a href="calendar.php" class="btn-primary">Calendrier</a>
                <a href="logout.php" class="btn-primary" style="margin-left: 10px;">Déconnexion</a>
            </div>
        </header>

        <div class="board">
            <?php foreach (Task::getStatusLabels() as $status => $label): ?>
            <div class="column">
                <h2 class="column-header"><?= $label ?></h2>
                <div class="task-list">
                    <?php foreach ($tasksByStatus[$status] as $task): ?>
                    <div class="task" data-task-id="<?= $task['id'] ?>">
                        <div class="task-header">
                            <h3 class="task-title"><?= Utility::h($task['title']) ?></h3>
                            <div class="task-actions">
                                <?php if ($userObj->isManager() || $task['created_by'] === $userObj->getLoggedInUserId() || $task['assigned_to'] === $userObj->getLoggedInUserId()): ?>
                                    <button onclick="editTask(<?= $task['id'] ?>)" title="Modifier">✏️</button>
                                    <?php if ($userObj->isManager() || $task['created_by'] === $userObj->getLoggedInUserId()): ?>
                                        <button onclick="deleteTask(<?= $task['id'] ?>)" title="Supprimer">🗑️</button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (!empty($task['description'])): ?>
                        <p class="task-description"><?= Utility::h($task['description']) ?></p>
                        <?php endif; ?>
                        <div class="task-footer">
                            <?php if ($task['due_date']): ?>
                            <span class="task-due">📅 <?= Utility::formatDate($task['due_date']) ?></span>
                            <?php endif; ?>
                            <?php if ($task['assigned_username']): ?>
                            <span class="task-assignee">👤 <?= Utility::h($task['assigned_username']) ?></span>
                            <?php endif; ?>
                            <?php if (isset($task['creator_username'])): ?>
                            <span class="task-creator">📝 <?= Utility::h($task['creator_username']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($userObj->isCollaborator()): ?>
        <!-- Formulaire pour les collaborateurs -->
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
                    <!-- Champ caché pour l'assignation à soi-même -->
                    <input type="hidden" name="assigned_to" value="<?= $userObj->getLoggedInUserId() ?>">
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
        <?php else: ?>
        <!-- Formulaire pour les managers -->
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
                        <select id="task-assigned" name="assigned_to">
                            <option value="">Personne</option>
                            <?php
                            $users = $userObj->getAllUsers();
                            foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>"><?= Utility::h($user['username']) ?></option>
                            <?php endforeach; ?>
                        </select>
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
        <?php endif; ?>
    </div>

    <!-- Bibliothèques JavaScript externes -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <!-- Nos fonctions JavaScript communes -->
    <script src="assets/js/common.js"></script>

    <script>
    // Variable globale pour stocker la liste des utilisateurs
    let users = [];

    // Quand la page est chargée, on récupère la liste des utilisateurs (seulement pour les managers)
    document.addEventListener('DOMContentLoaded', async () => {
        // Vérifier si l'utilisateur est un manager
        const isManager = <?= $userObj->isManager() ? 'true' : 'false' ?>;

        // Ne récupérer la liste des utilisateurs que si l'utilisateur est un manager
        if (isManager) {
            try {
                // Appel à l'API pour récupérer les utilisateurs
                const response = await axios.get(API_PATHS.GET_USERS);
                if (response.data.success) {
                    // Stockage des utilisateurs dans la variable globale
                    users = response.data.data;
                }
            } catch (error) {
                console.error('Erreur initialisation:', error);
            }
        }
    });

    // Fonction pour afficher le formulaire de création de tâche
    function showTaskForm() {
        toggleModal('taskForm', true);
    }

    // Fonction pour masquer le formulaire de création de tâche
    function hideTaskForm() {
        toggleModal('taskForm', false);
    }

    // Gestionnaire d'événement pour la soumission du formulaire de création de tâche
    document.getElementById('newTaskForm').addEventListener('submit', async (e) => {
        // Empêcher le comportement par défaut du formulaire (rechargement de la page)
        e.preventDefault();

        // Récupérer les données du formulaire
        const formData = {
            title: e.target.title.value,                    // Titre de la tâche
            description: e.target.description.value,        // Description de la tâche
            assigned_to: e.target.assigned_to.value || null, // Utilisateur assigné (ou null si non assigné)
            due_date: e.target.due_date.value,              // Date limite
            action: 'create'                                // Action à effectuer
        };

        try {
            console.log('Envoi des données:', formData);
            console.log('URL:', API_PATHS.CREATE_TASK);

            // Envoyer les données au serveur
            const response = await axios.post(API_PATHS.CREATE_TASK, formData, {
                headers: { 'Content-Type': 'application/json' }
            });

            if (response.data.success) {
                // Si la création a réussi, recharger la page pour afficher la nouvelle tâche
                window.location.reload();
            } else {
                // Si la création a échoué, afficher l'erreur
                alert('Erreur: ' + (response.data.error || 'Inconnue'));
            }
        } catch (error) {
            console.error('Erreur détaillée:', error);
            // En cas d'erreur réseau ou autre, utiliser notre fonction de gestion d'erreur
            handleApiError(error, 'Erreur lors de la création de la tâche');
        }
    });

    // Fonction pour supprimer une tâche
    async function deleteTask(taskId) {
        // Demander confirmation avant de supprimer
        if (!confirm('Supprimer cette tâche définitivement ?')) return;

        try {
            // Envoyer la demande de suppression au serveur
            const response = await axios.post(API_PATHS.DELETE_TASK, {
                task_id: taskId,
                action: 'delete'
            }, {
                headers: { 'Content-Type': 'application/json' }
            });

            if (response.data.success) {
                // Si la suppression a réussi, recharger la page
                window.location.reload();
            } else {
                // Si la suppression a échoué, afficher l'erreur
                handleApiError({ response: { data: response.data } }, 'Suppression échouée');
            }
        } catch (error) {
            console.error('Erreur détaillée:', error);
            // En cas d'erreur réseau ou autre
            handleApiError(error, 'Erreur lors de la suppression');
        }
    }

    // Fonction pour éditer une tâche existante
    async function editTask(taskId) {
        try {
            // Récupérer les détails de la tâche depuis le serveur
            const response = await axios.get(`${API_PATHS.GET_TASK}&id=${taskId}`);
            console.log('URL appelée pour édition:', `${API_PATHS.GET_TASK}&id=${taskId}`);
            console.log('Réponse:', response.data);

            if (!response.data.success) {
                // Si la récupération a échoué, lancer une erreur
                throw new Error(response.data.error);
            }

            // Afficher le formulaire d'édition avec les données de la tâche
            showEditForm(response.data.data);
        } catch (error) {
            console.error('Erreur détaillée:', error);
            // En cas d'erreur
            handleApiError(error, 'Impossible de récupérer les détails de la tâche');
        }
    }

    // Fonction pour afficher le formulaire d'édition d'une tâche
    function showEditForm(task) {
        // Récupérer des informations sur l'utilisateur actuel
        const isCollaborator = <?= $userObj->isCollaborator() ? 'true' : 'false' ?>; // Est-ce un collaborateur?
        const currentUserId = <?= $userObj->getLoggedInUserId() ?>;                  // ID de l'utilisateur
        const currentUsername = '<?= Utility::h($_SESSION['username'] ?? 'Vous-même') ?>'; // Nom d'utilisateur

        // Définir le HTML du formulaire en fonction du rôle de l'utilisateur
        let formHTML = '';

        if (isCollaborator) {
            // Formulaire pour les collaborateurs (sans champ d'assignation)
            formHTML = `
            <div class="modal-overlay" id="editModal">
                <div class="modal-content">
                    <h3>Modifier la tâche</h3>
                    <button type="button" class="modal-close" onclick="closeEditForm()">&times;</button>
                    <form id="editForm">
                        <!-- ID de la tâche (caché) -->
                        <input type="hidden" name="id" value="${task.id}">
                        <input type="hidden" name="assigned_to" value="${currentUserId}">

                        <!-- Titre de la tâche -->
                        <div class="form-group">
                            <label for="edit-task-title">Titre:</label>
                            <input type="text" id="edit-task-title" name="title" value="${task.title}" required>
                        </div>

                        <!-- Description de la tâche -->
                        <div class="form-group">
                            <label for="edit-task-description">Description:</label>
                            <textarea id="edit-task-description" name="description">${task.description || ''}</textarea>
                        </div>

                        <!-- Statut de la tâche -->
                        <div class="form-group">
                            <label for="edit-task-status">Statut:</label>
                            <select id="edit-task-status" name="status">
                                ${Object.entries(STATUS_LABELS).map(([value, label]) =>
                                    `<option value="${value}" ${task.status === value ? 'selected' : ''}>${label}</option>`
                                ).join('')}
                            </select>
                        </div>

                        <!-- Date limite de la tâche -->
                        <div class="form-group">
                            <label for="edit-task-due-date">Date limite:</label>
                            <input type="datetime-local" id="edit-task-due-date" name="due_date" value="${task.due_date ? task.due_date.slice(0, 16) : ''}">
                        </div>

                        <!-- Boutons d'action -->
                        <div class="form-buttons">
                            <button type="submit" class="btn-primary">Enregistrer</button>
                            <button type="button" class="btn-secondary" onclick="closeEditForm()">Annuler</button>
                        </div>
                    </form>
                </div>
            </div>`;
        } else {
            // Formulaire pour les managers (avec champ d'assignation)
            formHTML = `
            <div class="modal-overlay" id="editModal">
                <div class="modal-content">
                    <h3>Modifier la tâche</h3>
                    <button type="button" class="modal-close" onclick="closeEditForm()">&times;</button>
                    <form id="editForm">
                        <!-- ID de la tâche (caché) -->
                        <input type="hidden" name="id" value="${task.id}">

                        <!-- Titre de la tâche -->
                        <div class="form-group">
                            <label for="edit-task-title">Titre:</label>
                            <input type="text" id="edit-task-title" name="title" value="${task.title}" required>
                        </div>

                        <!-- Description de la tâche -->
                        <div class="form-group">
                            <label for="edit-task-description">Description:</label>
                            <textarea id="edit-task-description" name="description">${task.description || ''}</textarea>
                        </div>

                        <!-- Statut de la tâche -->
                        <div class="form-group">
                            <label for="edit-task-status">Statut:</label>
                            <select id="edit-task-status" name="status">
                                ${Object.entries(STATUS_LABELS).map(([value, label]) =>
                                    `<option value="${value}" ${task.status === value ? 'selected' : ''}>${label}</option>`
                                ).join('')}
                            </select>
                        </div>

                        <!-- Assignation de la tâche -->
                        <div class="form-group">
                            <label for="edit-task-assigned">Assigner à:</label>
                            <select id="edit-task-assigned" name="assigned_to">
                                <option value="">Personne</option>
                                ${users.map(user => `<option value="${user.id}" ${task.assigned_to == user.id ? 'selected' : ''}>${user.username}</option>`).join('')}
                            </select>
                        </div>

                        <!-- Date limite de la tâche -->
                        <div class="form-group">
                            <label for="edit-task-due-date">Date limite:</label>
                            <input type="datetime-local" id="edit-task-due-date" name="due_date" value="${task.due_date ? task.due_date.slice(0, 16) : ''}">
                        </div>

                        <!-- Boutons d'action -->
                        <div class="form-buttons">
                            <button type="submit" class="btn-primary">Enregistrer</button>
                            <button type="button" class="btn-secondary" onclick="closeEditForm()">Annuler</button>
                        </div>
                    </form>
                </div>
            </div>`;
        }

        // Ajouter le formulaire à la page
        document.body.insertAdjacentHTML('beforeend', formHTML);

        // Ajouter un gestionnaire d'événement pour la soumission du formulaire
        document.getElementById('editForm').addEventListener('submit', submitEditForm);
    }

    // Fonction pour soumettre le formulaire d'édition
    async function submitEditForm(e) {
        // Empêcher le comportement par défaut du formulaire
        e.preventDefault();

        // Récupérer les données du formulaire
        const formData = {
            id: e.target.id.value,                      // ID de la tâche
            title: e.target.title.value,                // Titre
            description: e.target.description.value,    // Description
            status: e.target.status.value,              // Statut (todo, in_progress, done)
            assigned_to: e.target.assigned_to.value || null, // Utilisateur assigné
            due_date: e.target.due_date.value,          // Date limite
            action: 'update'                            // Action à effectuer
        };

        try {
            console.log('Envoi des données de mise à jour:', formData);
            console.log('URL:', API_PATHS.EDIT_TASK);

            // Envoyer les données au serveur
            const response = await axios.post(API_PATHS.EDIT_TASK, formData, {
                headers: { 'Content-Type': 'application/json' }
            });

            if (response.data.success) {
                // Si la modification a réussi, recharger la page
                window.location.reload();
            } else {
                // Si la modification a échoué, afficher l'erreur
                handleApiError({ response: { data: response.data } }, 'Modification échouée');
            }
        } catch (error) {
            console.error('Erreur détaillée:', error);
            // En cas d'erreur réseau ou autre
            handleApiError(error, 'Erreur lors de la modification');
        }
    }

    // Fonction pour fermer le formulaire d'édition
    function closeEditForm() {
        // Supprimer la modale du DOM
        document.getElementById('editModal').remove();
    }
    </script>
</body>
</html>
