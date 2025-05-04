<?php
/**
 * Fichier: get_task_details.php
 *
 * Ce fichier est maintenant un simple redirecteur vers get_task.php avec le paramètre mode=calendar.
 * Il est conservé pour assurer la compatibilité avec le code existant qui pourrait encore y faire référence.
 */

// Récupère l'ID de la tâche depuis les paramètres GET
$taskId = $_GET['id'] ?? null;

// Redirige vers get_task.php avec les bons paramètres
header('Location: get_task.php?id=' . $taskId . '&mode=calendar');
exit;
?>
