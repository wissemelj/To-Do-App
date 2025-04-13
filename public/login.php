<?php
require_once '../src/includes/auth.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Task Manager</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/login.css">

</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>Bienvenue !</h1>
                <p>Connectez-vous à votre espace de travail</p>
            </div>

            <form id="loginForm" action="../src/actions/login_action.php" method="POST">
                <div class="input-group">
                    <input type="text" name="username_or_email" placeholder="Nom d'utilisateur ou Email" required>
                    <i class="fas fa-user input-icon"></i>
                </div>

                <div class="input-group">
                    <input type="password" name="password" placeholder="Mot de passe" required>
                    <i class="fas fa-lock input-icon"></i>
                </div>

                <button type="submit" class="login-button">Se connecter</button>
            </form>

            <div class="signup-link">
                Pas encore de compte ? <a href="register.php">S'inscrire</a>
            </div>
        </div>
    </div>

    <!-- Font Awesome pour les icônes -->
    <script src="https://kit.fontawesome.com/your-kit-code.js" crossorigin="anonymous"></script>
    
    <!-- Script de gestion du formulaire -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            try {
                const response = await axios.post(e.target.action, formData);
                if (response.data.success) {
                    window.location.href = 'index.php';
                } else {
                    alert(response.data.error || 'Identifiants incorrects');
                }
            } catch (error) {
                alert('Erreur de connexion');
            }
        });
    </script>
</body>
</html>