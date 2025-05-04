<?php
require_once __DIR__ . '/../src/includes/config.php';

// Log out user
$userObj->logoutUser();

// Redirect to login page
Utility::redirect(SITE_URL . '/login.php');
?>