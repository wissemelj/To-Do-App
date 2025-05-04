<?php
/**
 * Fichier: login_action.php
 *
 * Ce fichier gère l'authentification des utilisateurs dans l'application.
 * Il reçoit les identifiants de connexion (nom d'utilisateur/email et mot de passe)
 * via une requête POST, valide ces données, puis tente d'authentifier l'utilisateur.
 * Si l'authentification réussit, une session est créée pour l'utilisateur.
 *
 * Méthode HTTP: POST
 * Paramètres:
 *   - username_or_email: Nom d'utilisateur ou adresse email
 *   - password: Mot de passe
 * Réponse: JSON
 */
require_once '../includes/config.php';

// Définit le type de contenu de la réponse comme JSON
header('Content-Type: application/json');

// Vérifie que la méthode de requête est POST
// Cette API ne doit être accessible que via POST pour des raisons de sécurité
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Utility::jsonResponse(['success' => false, 'error' => 'Méthode non autorisée']);
}

// Récupère les données de connexion depuis la requête POST
// trim() supprime les espaces en début et fin de chaîne
$usernameOrEmail = trim($_POST['username_or_email']);
$password = $_POST['password'];

// Valide que les champs obligatoires sont présents et non vides
if (empty($usernameOrEmail) || empty($password)) {
    Utility::jsonResponse(['success' => false, 'error' => 'Tous les champs sont obligatoires']);
}

// Tente d'authentifier l'utilisateur en utilisant la méthode authenticate de la classe User
// Cette méthode vérifie les identifiants et crée une session si l'authentification réussit
$result = $userObj->authenticate($usernameOrEmail, $password);

// Renvoie le résultat de l'authentification au format JSON
// success: true si l'authentification a réussi, false sinon
// error: message d'erreur si l'authentification a échoué
Utility::jsonResponse($result);