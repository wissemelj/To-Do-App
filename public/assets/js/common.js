const STATUS_LABELS = {
    'todo': 'À Faire',
    'in_progress': 'En Cours',
    'done': 'Terminé'
};

const STATUS_COLORS = {
    'todo': '#667eea',
    'in_progress': '#ffb347',
    'done': '#77dd77'
};

const API_PATHS = {
    GET_USERS: '../src/actions/get_users.php',
    CREATE_TASK: '../src/actions/task_action.php',
    DELETE_TASK: '../src/actions/delete_task.php',
    GET_TASK: '../src/actions/get_task.php',
    EDIT_TASK: '../src/actions/edit_task.php',
    GET_CALENDAR_TASKS: '../src/actions/get_calendar_tasks.php',
    GET_TASK_DETAILS: '../src/actions/get_task_details.php'
};

function formatDate(dateString, options = {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
}) {
    if (!dateString) return 'Non défini';
    return new Date(dateString).toLocaleString('fr-FR', options);
}

function getStatusLabel(status) {
    return STATUS_LABELS[status] || 'Inconnu';
}

function getStatusColor(status) {
    return STATUS_COLORS[status] || STATUS_COLORS.todo;
}

function handleApiError(error, defaultMessage = 'Une erreur est survenue') {
    const errorMsg = error.response?.data?.error || error.message || defaultMessage;
    const isPermissionError = errorMsg.includes('accès non autorisé') || errorMsg.includes('Permission refusée');

    alert(errorMsg);

    if (isPermissionError) {
        window.location.reload();
    }

    return errorMsg;
}

function toggleModal(modalId, show) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = show ? 'flex' : 'none';
    }
}
