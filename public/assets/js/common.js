/**
 * Common JavaScript functions for the Task Manager application
 */

// Constants
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

// API paths
const API_PATHS = {
    GET_USERS: '../src/actions/get_users.php',
    CREATE_TASK: '../src/actions/task_action.php',
    DELETE_TASK: '../src/actions/delete_task.php',
    GET_TASK: '../src/actions/get_task.php',
    EDIT_TASK: '../src/actions/edit_task.php',
    GET_CALENDAR_TASKS: '../src/actions/get_calendar_tasks.php',
    GET_TASK_DETAILS: '../src/actions/get_task_details.php'
};

/**
 * Format a date string for display
 * @param {string|null} dateString - Date string from database
 * @param {Object} options - Formatting options for toLocaleString
 * @returns {string} Formatted date or 'Non défini' if date is null
 */
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

/**
 * Get the label for a status
 * @param {string} status - Status code
 * @returns {string} Status label
 */
function getStatusLabel(status) {
    return STATUS_LABELS[status] || 'Inconnu';
}

/**
 * Get the color for a status
 * @param {string} status - Status code
 * @returns {string} Status color (hex)
 */
function getStatusColor(status) {
    return STATUS_COLORS[status] || STATUS_COLORS.todo;
}

/**
 * Handle API errors
 * @param {Error} error - Error object
 * @param {string} defaultMessage - Default error message
 * @returns {string} Error message
 */
function handleApiError(error, defaultMessage = 'Une erreur est survenue') {
    const errorMsg = error.response?.data?.error || error.message || defaultMessage;
    
    // Check if it's a permission error
    const isPermissionError = errorMsg.includes('accès non autorisé') || 
                             errorMsg.includes('Permission refusée');
    
    alert(errorMsg);
    
    if (isPermissionError) {
        window.location.reload();
    }
    
    return errorMsg;
}

/**
 * Show/hide a modal
 * @param {string} modalId - ID of the modal element
 * @param {boolean} show - Whether to show or hide the modal
 */
function toggleModal(modalId, show) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = show ? 'flex' : 'none';
    }
}
