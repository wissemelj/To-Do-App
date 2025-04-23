<?php
require_once '../src/includes/auth.php';
requireLogin();

require_once '../src/includes/database.php';
require_once '../src/includes/utils.php';

$userId = getLoggedInUserId();

// --- Vérifier si le nom d'utilisateur est dans la session ---
ensureUsernameInSession($pdo, $userId);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier des Tâches</title>
    <link rel="stylesheet" href="assets/css/calendar.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
</head>

<body>
    <div class="container">
        <header class="dashboard-header">
            <div class="header-left">
                <h1 class="header-title">Calendrier des Tâches</h1>
                <span class="user-role"><?= isManager() ? 'Manager' : 'Collaborateur' ?></span>
            </div>
            <div>
                <a href="index.php" class="btn-primary">Retour au tableau</a>
                <a href="logout.php" class="btn-primary" style="margin-left: 10px;">Déconnexion</a>
            </div>
        </header>

        <div class="calendar-container">
            <div id="calendar"></div>
        </div>

        <!-- Task Modal -->
        <div id="taskModal" class="task-modal" style="display: none;">
            <div class="task-modal-content">
                <button class="task-modal-close" onclick="closeTaskModal()">&times;</button>
                <h3 class="task-modal-title" id="taskModalTitle"></h3>
                <div class="task-modal-body">
                    <p class="task-modal-info">
                        <span class="task-modal-label">Description:</span>
                        <span class="task-modal-value" id="taskModalDescription"></span>
                    </p>
                    <p class="task-modal-info">
                        <span class="task-modal-label">Statut:</span>
                        <span class="task-modal-value" id="taskModalStatus"></span>
                    </p>
                    <p class="task-modal-info">
                        <span class="task-modal-label">Date :</span>
                        <span class="task-modal-value" id="taskModalDueDate"></span>
                    </p>
                    <p class="task-modal-info">
                        <span class="task-modal-label">Assigné à:</span>
                        <span class="task-modal-value" id="taskModalAssignee"></span>
                    </p>
                    <p class="task-modal-info">
                        <span class="task-modal-label">Créé par:</span>
                        <span class="task-modal-value" id="taskModalCreator"></span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/fr.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="assets/js/common.js"></script>

    <script>

    // Initialisation du calendrier
    document.addEventListener('DOMContentLoaded', initializeCalendar);

    function initializeCalendar() {
        const calendarEl = document.getElementById('calendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'fr',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: fetchCalendarEvents,
            eventClick: handleEventClick,
            eventDidMount: styleEvent,
            dayMaxEventRows: 4,
            fixedWeekCount: false,
            dayHeaderFormat: { weekday: 'short', day: 'numeric' },
            height: 'parent',
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            }
        });

        calendar.render();

        // Ajuster la taille du calendrier lors du redimensionnement de la fenêtre
        window.addEventListener('resize', () => calendar.updateSize());
    }

    // Fonctions du calendrier
    async function fetchCalendarEvents(fetchInfo, successCallback) {
        try {
            const response = await axios.get(API_PATHS.GET_CALENDAR_TASKS);
            successCallback(response.data);
        } catch (error) {
            console.error('Erreur lors du chargement des événements:', error);
            successCallback([]);
        }
    }

    function handleEventClick(info) {
        showTaskDetails(info.event.id);
    }

    function styleEvent(info) {
        const status = info.event.extendedProps.status;
        info.el.style.background = getStatusColor(status);
        info.el.style.border = 'none';
        info.el.style.fontWeight = '500';
    }

    // Gestion des détails de tâche
    async function showTaskDetails(taskId) {
        try {
            const response = await axios.get(`${API_PATHS.GET_TASK_DETAILS}?id=${taskId}`);
            if (!response.data.success) {
                throw new Error(response.data.error || 'Erreur inconnue');
            }

            displayTaskDetails(response.data.task);
        } catch (error) {
            console.error('Erreur:', error);
            handleApiError(error, 'Impossible de charger les détails de la tâche');
        }
    }

    function displayTaskDetails(task) {
        document.getElementById('taskModalTitle').textContent = task.title;
        document.getElementById('taskModalDescription').textContent = task.description || 'Aucune description';
        document.getElementById('taskModalStatus').textContent = getStatusLabel(task.status);
        document.getElementById('taskModalDueDate').textContent = formatDate(task.due_date);
        document.getElementById('taskModalAssignee').textContent = task.assigned_username || 'Non assigné';
        document.getElementById('taskModalCreator').textContent = task.creator_username || 'Inconnu';

        toggleModal('taskModal', true);
    }

    function closeTaskModal() {
        toggleModal('taskModal', false);
    }

    // Fermer la modal en cliquant en dehors
    document.getElementById('taskModal').addEventListener('click', (e) => {
        if (e.target === document.getElementById('taskModal')) {
            closeTaskModal();
        }
    });
</script>

</body>
</html>