<?php
require_once '../src/includes/config.php';

// Check if user is logged in
$userObj->requireLogin(SITE_URL . '/login.php');

// Ensure username is in session
$userObj->ensureUsernameInSession();
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
                <span class="user-role"><?= $userObj->isManager() ? 'Manager' : 'Collaborateur' ?></span>
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
    document.addEventListener('DOMContentLoaded', () => {
        initializeCalendar();

        // Modal click handler
        document.getElementById('taskModal').addEventListener('click', (e) => {
            if (e.target === document.getElementById('taskModal')) {
                closeTaskModal();
            }
        });
    });

    function initializeCalendar() {
        const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
            initialView: 'dayGridMonth',
            locale: 'fr',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: async (info, successCallback) => {
                try {
                    const response = await axios.get(API_PATHS.GET_CALENDAR_TASKS);
                    successCallback(response.data);
                } catch (error) {
                    console.error('Erreur:', error);
                    successCallback([]);
                }
            },
            eventClick: info => showTaskDetails(info.event.id),
            eventDidMount: info => {
                info.el.style.background = getStatusColor(info.event.extendedProps.status);
                info.el.style.border = 'none';
                info.el.style.fontWeight = '500';
            },
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
        window.addEventListener('resize', () => calendar.updateSize());
    }

    async function showTaskDetails(taskId) {
        try {
            const url = `${API_PATHS.GET_TASK_DETAILS}?id=${taskId}&mode=calendar`;
            console.log('URL appelée:', url);
            const response = await axios.get(url);
            if (!response.data.success) {
                throw new Error(response.data.error || 'Erreur inconnue');
            }

            const task = response.data.task;
            document.getElementById('taskModalTitle').textContent = task.title;
            document.getElementById('taskModalDescription').textContent = task.description || 'Aucune description';
            document.getElementById('taskModalStatus').textContent = getStatusLabel(task.status);
            document.getElementById('taskModalDueDate').textContent = formatDate(task.due_date);
            document.getElementById('taskModalAssignee').textContent = task.assigned_username || 'Non assigné';
            document.getElementById('taskModalCreator').textContent = task.creator_username || 'Inconnu';

            toggleModal('taskModal', true);
        } catch (error) {
            handleApiError(error, 'Impossible de charger les détails de la tâche');
        }
    }

    function closeTaskModal() {
        toggleModal('taskModal', false);
    }
</script>

</body>
</html>