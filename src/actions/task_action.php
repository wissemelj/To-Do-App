<?php
/**
 * Script de création d'une nouvelle tâche
 *
 * Ce script traite les requêtes AJAX pour créer une nouvelle tâche.
 * Il vérifie que l'utilisateur est connecté, valide les données reçues,
 * puis crée la tâche dans la base de données.
 *
 * Méthode HTTP: POST
 * Format de données: JSON
 * Réponse: JSON
 */

// Inclusion du fichier de configuration qui charge les classes et initialise les objets
require_once __DIR__ . '/../includes/config.php';

// Définit le type de contenu de la réponse comme JSON
header('Content-Type: application/json');

// Vérifie si l'utilisateur est connecté
// Si non, renvoie une erreur et arrête l'exécution du script
if (!$userObj->isLoggedIn()) {
    Utility::jsonResponse(['success' => false, 'error' => 'Non authentifié']);
}

// Récupère les données JSON envoyées dans le corps de la requête
// file_get_contents('php://input') lit les données brutes du corps de la requête
// json_decode convertit la chaîne JSON en tableau associatif PHP
$data = json_decode(file_get_contents('php://input'), true);

// Valide que les champs obligatoires sont présents et non vides
// Dans ce cas, seul le titre est obligatoire
$validation = Utility::validateRequired($data, ['title']);
if (!$validation['valid']) {
    Utility::jsonResponse(['success' => false, 'error' => 'Le titre est obligatoire']);
}

// Prépare les données de la tâche à partir des données reçues
// Nettoie et formate les données pour éviter les problèmes
$taskData = [
    'title' => trim($data['title']),                    // Titre de la tâche (obligatoire)
    'description' => trim($data['description'] ?? ''),  // Description (optionnelle)
    'assigned_to' => $data['assigned_to'] ?? null,      // Utilisateur assigné (optionnel)
    'due_date' => $data['due_date'] ?? null,            // Date d'échéance (optionnelle)
    'user_id' => $userObj->getLoggedInUserId()          // ID de l'utilisateur qui crée la tâche
];

// Crée la tâche dans la base de données en utilisant la méthode createTask de la classe Task
// Le résultat est un booléen indiquant si la création a réussi
$result = $taskObj->createTask($taskData);

// Renvoie le résultat au format JSON
// success: true si la création a réussi, false sinon
Utility::jsonResponse(['success' => $result]);
?>