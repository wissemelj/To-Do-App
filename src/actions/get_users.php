<?php
/**
 * Fichier: get_users.php
 *
 * Ce fichier gère la récupération de la liste de tous les utilisateurs de l'application.
 * Il vérifie que l'utilisateur est authentifié, puis renvoie la liste des utilisateurs
 * avec leur ID, nom d'utilisateur et rôle au format JSON.
 *
 * Cette liste est utilisée principalement pour le formulaire d'assignation des tâches,
 * permettant de sélectionner un utilisateur à qui assigner une tâche.
 *
 * Méthode HTTP: GET
 * Paramètres: Aucun
 * Réponse: JSON
 */
require_once __DIR__ . '/../includes/config.php';

// Définit le type de contenu de la réponse comme JSON
header('Content-Type: application/json');

// Vérifie si l'utilisateur est connecté
// Si non, renvoie une erreur et arrête l'exécution du script
if (!$userObj->isLoggedIn()) {
    Utility::jsonResponse(['success' => false, 'error' => 'Non authentifié']);
}

try {
    // Récupère la liste de tous les utilisateurs en utilisant la méthode getAllUsers de la classe User
    // Cette méthode renvoie un tableau avec l'ID, le nom d'utilisateur et le rôle de chaque utilisateur
    $users = $userObj->getAllUsers();

    // Renvoie la liste des utilisateurs au format JSON
    // success: true indique que la requête a réussi
    // data: contient le tableau des utilisateurs
    Utility::jsonResponse([
        'success' => true,
        'data' => $users
    ]);

} catch (Exception $e) {
    // En cas d'erreur, renvoie un message d'erreur au format JSON
    Utility::jsonResponse([
        'success' => false,
        'error' => 'Erreur: ' . $e->getMessage()  // Message d'erreur de l'exception
    ]);
}
?>