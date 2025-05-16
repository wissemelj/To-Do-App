/**
 * Fichier: common.js
 *
 * Ce fichier contient des fonctions JavaScript communes utilisées dans toute l'application TacTâche.
 * Il définit des constantes, des utilitaires et des fonctions de gestion d'API réutilisables
 * sur toutes les pages de l'application.
 *
 * Contenu:
 * - Constantes pour les libellés et couleurs des statuts de tâches
 * - Chemins vers les API du serveur
 * - Fonctions utilitaires (formatage de date, gestion des erreurs, etc.)
 * - Fonctions de manipulation de l'interface (modales, etc.)
 */

// Libellés des statuts de tâches pour l'affichage dans l'interface
const STATUS_LABELS = {
    'todo': 'À Faire',
    'in_progress': 'En Cours',
    'done': 'Terminé'
};

// Couleurs associées à chaque statut de tâche pour l'affichage visuel
const STATUS_COLORS = {
    'todo': '#667eea',       // Bleu pour les tâches à faire
    'in_progress': '#ffb347', // Orange pour les tâches en cours
    'done': '#77dd77'        // Vert pour les tâches terminées
};

// Chemins vers les API du serveur pour les différentes opérations CRUD
const API_PATHS = {
    GET_USERS: '../src/actions/get_users.php',             // Récupérer la liste des utilisateurs
    CREATE_TASK: '../src/actions/task_api.php',            // Créer une nouvelle tâche
    DELETE_TASK: '../src/actions/task_api.php',            // Supprimer une tâche
    GET_TASK: '../src/actions/task_api.php?action=get',    // Récupérer les détails d'une tâche pour l'édition
    EDIT_TASK: '../src/actions/task_api.php',              // Modifier une tâche
    GET_CALENDAR_TASKS: '../src/actions/task_api.php?action=calendar', // Récupérer les tâches pour le calendrier
    GET_TASK_DETAILS: '../src/actions/task_api.php?action=get'  // Récupérer les détails d'une tâche pour le calendrier
};

/**
 * Formate une date pour l'affichage
 * @param {string} dateString - La date à formater (au format ISO)
 * @param {object} options - Options de formatage
 * @returns {string} - La date formatée
 */
function formatDate(dateString, options = {
    year: 'numeric',    // Année (ex: 2023)
    month: '2-digit',   // Mois (ex: 01)
    day: '2-digit',     // Jour (ex: 31)
    hour: '2-digit',    // Heure (ex: 14)
    minute: '2-digit'   // Minute (ex: 30)
}) {
    // Si la date est vide, on retourne "Non défini"
    if (!dateString) return 'Non défini';

    // Sinon, on formate la date selon les options
    return new Date(dateString).toLocaleString('fr-FR', options);
}

/**
 * Récupère le libellé d'un statut
 * @param {string} status - Code du statut (todo, in_progress, done)
 * @returns {string} - Libellé du statut
 */
function getStatusLabel(status) {
    return STATUS_LABELS[status] || 'Inconnu';
}

/**
 * Récupère la couleur associée à un statut
 * @param {string} status - Code du statut (todo, in_progress, done)
 * @returns {string} - Code couleur hexadécimal
 */
function getStatusColor(status) {
    return STATUS_COLORS[status] || STATUS_COLORS.todo;
}

/**
 * Gère les erreurs d'API et affiche un message approprié
 * @param {Error} error - L'erreur survenue
 * @param {string} defaultMessage - Message par défaut si aucun message d'erreur n'est disponible
 * @returns {string} - Le message d'erreur
 */
function handleApiError(error, defaultMessage = 'Une erreur est survenue') {
    // Récupérer le message d'erreur depuis la réponse de l'API ou utiliser le message par défaut
    const errorMsg = error.response?.data?.error || error.message || defaultMessage;

    // Vérifier si c'est une erreur de permission
    const isPermissionError = errorMsg.includes('accès non autorisé') ||
                             errorMsg.includes('Permission refusée');

    // Afficher l'erreur à l'utilisateur
    alert(errorMsg);

    // Si c'est une erreur de permission, recharger la page
    if (isPermissionError) {
        window.location.reload();
    }

    return errorMsg;
}

/**
 * Affiche ou masque une fenêtre modale
 * @param {string} modalId - L'identifiant de la modale
 * @param {boolean} show - true pour afficher, false pour masquer
 */
function toggleModal(modalId, show) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = show ? 'flex' : 'none';
    }
}
