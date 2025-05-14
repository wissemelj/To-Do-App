<?php
/**
 * Fichier: logout.php
 *
 * Script de déconnexion de l'application TacTâche.
 * Ce script termine la session de l'utilisateur et le redirige vers la page de connexion.
 *
 * Fonctionnement:
 * 1. Inclusion du fichier de configuration
 * 2. Appel de la méthode logoutUser() qui détruit la session
 * 3. Redirection vers la page de connexion
 *
 * Sécurité:
 * - Destruction complète de la session pour éviter les fuites de données
 */
require_once __DIR__ . '/../src/includes/config.php';

// Déconnexion de l'utilisateur
$userObj->logoutUser();

// Redirection vers la page de connexion
Utility::redirect(SITE_URL . '/login.php');
?>