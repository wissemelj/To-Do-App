<?php
/**
 * Fichier: calendar.php
 *
 * Page de calendrier de l'application TacTâche qui affiche les tâches sous forme
 * de calendrier interactif, permettant une visualisation temporelle des échéances.
 *
 * Fonctionnalités:
 * - Affichage des tâches dans un calendrier mensuel, hebdomadaire ou quotidien
 * - Visualisation des tâches avec code couleur selon leur statut
 * - Consultation des détails d'une tâche en cliquant sur l'événement
 * - Navigation temporelle (mois précédent/suivant, aujourd'hui)
 *
 * Dépendances:
 * - Bibliothèque FullCalendar pour l'affichage du calendrier
 * - Axios pour les requêtes AJAX
 *
 * Sécurité:
 * - Accès restreint aux utilisateurs authentifiés
 */

require_once '../src/includes/config.php';

// Vérifier que l'utilisateur est connecté (sinon redirection vers login.php)
$userObj->requireLogin(SITE_URL . '/login.php');

// S'assurer que le nom d'utilisateur est dans la session
$userObj->ensureUsernameInSession();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier des Tâches</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/calendar.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Custom styles for calendar buttons */
        .fc .fc-button-primary {
            position: relative;
            min-width: 38px;
            justify-content: center;
            border-radius: 6px !important;
        }

        /* Remove default button group styling */
        .fc .fc-button-group > .fc-button {
            border-radius: 6px !important;
            margin: 0 !important;
        }

        /* View buttons should have consistent width */
        .fc .fc-dayGridMonth-button,
        .fc .fc-timeGridWeek-button,
        .fc .fc-timeGridDay-button,
        .fc .fc-today-button {
            min-width: 110px;
        }

        /* Mobile styles for buttons */
        @media (max-width: 768px) {
            .fc .fc-dayGridMonth-button,
            .fc .fc-timeGridWeek-button,
            .fc .fc-timeGridDay-button,
            .fc .fc-today-button {
                min-width: 90px;
            }
        }

        @media (max-width: 480px) {
            .fc .fc-dayGridMonth-button,
            .fc .fc-timeGridWeek-button,
            .fc .fc-timeGridDay-button,
            .fc .fc-today-button {
                min-width: 70px;
                padding: 6px 8px !important;
            }
        }

        /* Add icons to prev/next buttons */
        .fc .fc-prev-button:before {
            content: "\f053"; /* chevron-left */
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
        }

        .fc .fc-next-button:before {
            content: "\f054"; /* chevron-right */
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
        }

        .fc .fc-today-button:before {
            content: "\f133"; /* calendar-check */
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            margin-right: 5px;
        }

        .fc .fc-dayGridMonth-button:before {
            content: "\f073"; /* calendar */
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            margin-right: 5px;
        }

        .fc .fc-timeGridWeek-button:before {
            content: "\f0ce"; /* table */
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            margin-right: 5px;
        }

        .fc .fc-timeGridDay-button:before {
            content: "\f783"; /* calendar-day */
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            margin-right: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <header class="dashboard-header">
            <div class="header-left">
                <h1 class="header-title">TacTâche - Calendrier</h1>
                <span class="user-role"><?= $userObj->isManager() ? 'Manager' : 'Collaborateur' ?></span>
            </div>
            <div class="header-actions">
                <a href="index.php" class="btn-primary"><i class="fas fa-tasks"></i> Backlog</a>
                <a href="logout.php" class="btn-secondary"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
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

        // Initialisation terminée
    });

    // Calendar initialization functions

    function initializeCalendar() {
        const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
            initialView: 'dayGridMonth',
            locale: 'fr',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            // Customize button text with icons
            buttonText: {
                today: 'Aujourd\'hui',
                month: 'Mois',
                week: 'Semaine',
                day: 'Jour'
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
            const url = `${API_PATHS.GET_TASK_DETAILS}&id=${taskId}&mode=calendar`;
            console.log('URL appelée:', url);
            const response = await axios.get(url);
            if (!response.data.success) {
                throw new Error(response.data.error || 'Erreur inconnue');
            }

            // La réponse contient la tâche dans response.data.task pour le mode calendar
            const task = response.data.task;
            if (!task) {
                throw new Error('Données de tâche non trouvées dans la réponse');
            }

            document.getElementById('taskModalTitle').textContent = task.title;
            document.getElementById('taskModalDescription').textContent = task.description || 'Aucune description';
            document.getElementById('taskModalStatus').textContent = getStatusLabel(task.status);
            document.getElementById('taskModalDueDate').textContent = formatDate(task.due_date);
            document.getElementById('taskModalAssignee').textContent = task.assigned_username || 'Non assigné';
            document.getElementById('taskModalCreator').textContent = task.creator_username || 'Inconnu';

            toggleModal('taskModal', true);
        } catch (error) {
            console.error('Erreur détaillée:', error);
            handleApiError(error, 'Impossible de charger les détails de la tâche');
        }
    }

    function closeTaskModal() {
        toggleModal('taskModal', false);
    }
</script>

</body>
</html>