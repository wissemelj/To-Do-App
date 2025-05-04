<?php
/**
 * Fichier: get_calendar_tasks.php
 *
 * Ce fichier gère la récupération des tâches pour l'affichage dans le calendrier.
 * Il vérifie que l'utilisateur est authentifié, puis récupère toutes les tâches
 * formatées pour l'affichage dans le composant FullCalendar.
 *
 * Les tâches sont renvoyées avec un format spécifique requis par FullCalendar,
 * notamment avec la propriété 'start' pour la date d'échéance et une couleur
 * différente selon le statut de la tâche.
 *
 * Méthode HTTP: GET
 * Paramètres: Aucun
 * Réponse: JSON (tableau d'événements pour FullCalendar)
 */
require_once __DIR__ . '/../includes/config.php';

// Définit le type de contenu de la réponse comme JSON
header('Content-Type: application/json');

// Vérifie si l'utilisateur est connecté
// Si non, renvoie un tableau vide et arrête l'exécution du script
if (!$userObj->isLoggedIn()) {
    echo json_encode([]);
    exit();
}

// Récupère les tâches formatées pour le calendrier en utilisant la méthode getCalendarTasks de la classe Task
// Cette méthode renvoie un tableau de tâches avec le format requis par FullCalendar
$events = $taskObj->getCalendarTasks();

// Renvoie les événements au format JSON
// Chaque événement contient l'ID, le titre, la date (start) et une couleur selon le statut
echo json_encode($events);
?>