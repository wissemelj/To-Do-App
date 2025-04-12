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
    <title>Calendrier des Tâches - Task Manager</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --text-dark: #2d3748;
            --text-light: #718096;
        }

        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            padding: 20px;
            font-family: 'Segoe UI', sans-serif;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .header-title {
            font-size: 1.8rem;
            color: var(--text-dark);
            margin: 0;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: transform 0.2s ease;
            font-weight: 500;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.15);
        }

        .calendar-container {
            padding: 15px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            margin: 20px auto;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            min-height: 60vh;
        }

        /* FullCalendar Customization */
        #calendar {
            height: 650px;
        }

        .fc-toolbar {
            flex-direction: column;
            gap: 8px;
            padding: 8px 0;
            margin-bottom: 10px !important;
        }

        .fc-toolbar-title {
            font-size: 1.4rem;
            color: var(--text-dark);
            font-weight: 600;
        }

        .fc-button-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%) !important;
            border: none !important;
            border-radius: 6px !important;
            padding: 6px 12px !important;
            font-size: 0.85rem !important;
            transition: transform 0.2s ease !important;
        }

        .fc-button-primary:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.15) !important;
        }

        .fc-col-header-cell {
            padding: 6px 0 !important;
        }

        .fc-col-header-cell-cushion {
            font-size: 0.9rem;
            color: var(--text-dark);
        }

        .fc-daygrid-day-number {
            font-size: 0.8rem;
            padding: 2px 4px !important;
        }

        .fc-event {
            border: none;
            border-radius: 4px;
            padding: 2px 4px;
            margin: 1px 0;
            font-size: 0.75rem;
            line-height: 1.1;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .fc-event:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .fc-daygrid-event-dot {
            display: none;
        }

        @media (max-width: 1200px) {
            .container {
                max-width: 95%;
            }

            .dashboard-header {
                flex-direction: column;
                gap: 12px;
                text-align: center;
            }

            #calendar {
                height: 550px;
            }

            .fc-toolbar-title {
                font-size: 1.2rem;
            }
        }

        @media (max-width: 768px) {
            #calendar {
                height: 450px;
            }

            .fc-header-toolbar .fc-toolbar-chunk {
                display: flex;
                flex-wrap: wrap;
                gap: 4px;
                justify-content: center;
            }

            .btn-primary {
                padding: 8px 15px;
                font-size: 0.8rem;
            }

            .header-title {
                font-size: 1.4rem;
            }
        }

        @media (max-width: 480px) {
            #calendar {
                height: 400px;
            }

            .fc-toolbar-title {
                font-size: 1rem;
            }

            .fc-button-primary {
                padding: 4px 8px !important;
                font-size: 0.75rem !important;
            }

            .fc-col-header-cell-cushion {
                font-size: 0.75rem;
            }
        }

        /* Task Modal Styles */
        .task-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            backdrop-filter: blur(3px);
        }

        .task-modal-content {
            background: white;
            padding: 25px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            position: relative;
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .task-modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: var(--text-light);
        }

        .task-modal-title {
            margin: 0 0 15px 0;
            color: var(--text-dark);
        }

        .task-modal-info {
            margin-bottom: 10px;
        }

        .task-modal-label {
            color: var(--text-dark);
            font-weight: 600;
        }

        .task-modal-value {
            color: var(--text-light);
            margin-left: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="dashboard-header">
            <h1 class="header-title">Calendrier des Tâches</h1>
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
                        <span class="task-modal-label">Date limite:</span>
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

    function getStatusLabel(status) {
        const labels = {
            'todo': 'À Faire',
            'in_progress': 'En Cours',
            'done': 'Terminé'
        };
        return labels[status] || 'Inconnu';
    }

    function closeTaskModal() {
        document.getElementById('taskModal').style.display = 'none';
    }

    // Close modal when clicking outside
    document.getElementById('taskModal').addEventListener('click', (e) => {
        if (e.target === document.getElementById('taskModal')) {
            closeTaskModal();
        }
    });
    </script>
</body>
</html>