<?php
/**
 * Fichier: register_action.php
 *
 * Ce fichier gère l'inscription de nouveaux utilisateurs dans l'application.
 * Il reçoit les données d'inscription (nom d'utilisateur, email, mot de passe et rôle)
 * via une requête POST, valide ces données, puis crée un nouvel utilisateur dans la base de données.
 *
 * Méthode HTTP: POST
 * Paramètres:
 *   - username: Nom d'utilisateur (unique)
 *   - email: Adresse email (unique)
 *   - password: Mot de passe
 *   - role: Rôle de l'utilisateur ( 'manager' ou 'collaborator')
 * Réponse: JSON
 */
require_once __DIR__ . '/../includes/config.php';

// Définit le type de contenu de la réponse comme JSON
header('Content-Type: application/json');

// Vérifie que la méthode de requête est POST
// Cette API ne doit être accessible que via POST pour des raisons de sécurité
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Utility::jsonResponse(['success' => false, 'error' => 'Méthode non autorisée']);
}

// Récupère et nettoie les données du formulaire d'inscription
// trim() supprime les espaces en début et fin de chaîne
// L'opérateur ?? fournit une valeur par défaut si la variable n'existe pas
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Valide que tous les champs obligatoires sont présents et non vides
if (empty($username) || empty($email) || empty($password)) {
    Utility::jsonResponse(['success' => false, 'error' => 'Tous les champs sont requis']);
}

// Prépare les données de l'utilisateur pour l'inscription
$userData = [
    'username' => $username,                // Nom d'utilisateur (doit être unique)
    'email' => $email,                      // Email (doit être unique)
    'password' => $password,                // Mot de passe (sera haché dans la méthode register)
    'role' => isset($_POST['role']) && $_POST['role'] === 'manager' ? 'manager' : 'collaborator'  // Rôle (manager ou collaborator)
];

// Crée le nouvel utilisateur en utilisant la méthode register de la classe User
// Cette méthode vérifie si le nom d'utilisateur et l'email sont uniques avant de créer l'utilisateur
$result = $userObj->register($userData);

// Renvoie le résultat de l'inscription au format JSON
// success: true si l'inscription a réussi, false sinon
// error: message d'erreur si l'inscription a échoué
Utility::jsonResponse($result);
?>