<?php
/**
 * Fichier: edit_task.php
 *
 * Ce fichier gère la modification d'une tâche existante dans l'application.
 * Il reçoit les données modifiées via une requête AJAX, vérifie que l'utilisateur
 * est authentifié et a les permissions nécessaires, valide les données reçues
 * et met à jour la tâche dans la base de données.
 *
 * Méthode HTTP: POST
 * Format de données: JSON
 * Réponse: JSON
 */
require_once __DIR__ . '/../includes/config.php';

// Définit le type de contenu de la réponse comme JSON
header('Content-Type: application/json');

// Vérifie si l'utilisateur est connecté et si la méthode de requête est POST
// Si non, renvoie une erreur et arrête l'exécution du script
if (!$userObj->isLoggedIn() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    Utility::jsonResponse(['success' => false, 'error' => 'Accès non autorisé']);
}

// Récupère les données JSON envoyées dans le corps de la requête
// file_get_contents('php://input') lit les données brutes du corps de la requête
// json_decode convertit la chaîne JSON en tableau associatif PHP
$input = json_decode(file_get_contents('php://input'), true);

// Valide que les champs obligatoires sont présents et non vides
// Pour la modification d'une tâche, l'ID et le titre sont obligatoires
$validation = Utility::validateRequired($input, ['id', 'title']);
if (!$validation['valid']) {
    Utility::jsonResponse(['success' => false, 'error' => implode(', ', $validation['errors'])]);
}

// Vérifie si l'utilisateur a le droit de modifier cette tâche
// Cette vérification utilise la méthode canModifyTask de la classe User
if (!$userObj->canModifyTask((int)$input['id'], $taskObj)) {
    Utility::jsonResponse(['success' => false, 'error' => 'Permission refusée']);
}

// Prépare les données de la tâche à partir des données reçues
// Convertit et formate les données pour éviter les problèmes
$taskData = [
    'id' => (int)$input['id'],                     // ID de la tâche (obligatoire)
    'title' => $input['title'],                    // Titre de la tâche (obligatoire)
    'description' => $input['description'] ?? null, // Description (optionnelle)
    'status' => $input['status'] ?? 'todo',        // Statut (optionnel, 'todo' par défaut)
    'due_date' => $input['due_date'] ?? null,      // Date d'échéance (optionnelle)
    'assigned_to' => $input['assigned_to'] ?? null // Utilisateur assigné (optionnel)
];

// Met à jour la tâche dans la base de données en utilisant la méthode updateTask de la classe Task
// Le résultat est un booléen indiquant si la mise à jour a réussi
$result = $taskObj->updateTask($taskData);

// Renvoie le résultat au format JSON
// success: true si la mise à jour a réussi, false sinon
Utility::jsonResponse(['success' => $result]);
?>