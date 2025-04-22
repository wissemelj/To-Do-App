<?php
require_once '../src/includes/auth.php';
requireLogin();

require_once '../src/includes/database.php';

$userId = getLoggedInUserId();
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

    <script>
    document.addEventListener('DOMContentLoaded', async () => {
        const calendarEl = document.getElementById('calendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'fr',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: async (fetchInfo, successCallback) => {
                try {
                    const response = await axios.get('../src/actions/get_calendar_tasks.php');
                    successCallback(response.data);
                } catch (error) {
                    console.error('Erreur:', error);
                }
            },
            eventClick: function(info) {
                showTaskDetails(info.event.id);
            },
            eventDidMount: function(info) {
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

        function getStatusColor(status) {
            const colors = {
                'todo': '#667eea',
                'in_progress': '#ffb347',
                'done': '#77dd77'
            };
            return colors[status] || '#667eea';
        }

        window.addEventListener('resize', () => {
            calendar.updateSize();
        });
    });

    async function showTaskDetails(taskId) {
        try {
            const response = await axios.get(`../src/actions/get_task_details.php?id=${taskId}`);
            if (!response.data.success) {
                throw new Error(response.data.error || 'Erreur inconnue');
            }

            const task = response.data.task;
            document.getElementById('taskModalTitle').textContent = task.title;
            document.getElementById('taskModalDescription').textContent = task.description || 'Aucune description';
            document.getElementById('taskModalStatus').textContent = getStatusLabel(task.status);
            document.getElementById('taskModalDueDate').textContent = task.due_date ?
                new Date(task.due_date).toLocaleString('fr-FR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit'
                }) : 'Non défini';
            document.getElementById('taskModalAssignee').textContent = task.assigned_username || 'Non assigné';
            document.getElementById('taskModalCreator').textContent = task.creator_username || 'Inconnu';

            document.getElementById('taskModal').style.display = 'flex';

        } catch (error) {
            console.error('Erreur:', error);
            alert(error.message || 'Impossible de charger les détails de la tâche');
        }
    }

    function closeTaskModal() {
        document.getElementById('taskModal').style.display = 'none';
    }

    function getStatusLabel(status) {
        const labels = {
            'todo': 'À Faire',
            'in_progress': 'En Cours',
            'done': 'Terminé'
        };
        return labels[status] || 'Inconnu';
    }

    document.getElementById('taskModal').addEventListener('click', (e) => {
        if (e.target === document.getElementById('taskModal')) {
            closeTaskModal();
        }
    });
</script>

</body>
</html>