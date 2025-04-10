<?php
require_once __DIR__ . '/../src/includes/auth.php';
logoutUser();
header("Location: login.php");
?>