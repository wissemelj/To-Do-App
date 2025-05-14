<?php
/**
 * Fichier: register.php
 *
 * Page d'inscription de l'application TacTâche permettant aux nouveaux utilisateurs
 * de créer un compte pour accéder à l'application.
 *
 * Fonctionnalités:
 * - Formulaire d'inscription avec nom d'utilisateur, email, mot de passe et rôle
 * - Redirection automatique vers le tableau de bord si déjà connecté
 * - Lien vers la page de connexion pour les utilisateurs existants
 *
 * Traitement:
 * - La soumission du formulaire est gérée par AJAX via Axios
 * - L'inscription est traitée par register_action.php
 * - Après inscription réussie, redirection vers la page de connexion
 *
 * Sécurité:
 * - Redirection automatique si l'utilisateur est déjà connecté
 * - Validation des données d'inscription côté serveur
 * - Hachage sécurisé du mot de passe lors de l'enregistrement
 */
require_once '../src/includes/config.php';

// Si l'utilisateur est déjà connecté, rediriger vers la page d'accueil
if ($userObj->isLoggedIn()) {
    Utility::redirect('index.php');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - TacTâche</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/register.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>Rejoignez TacTâche</h1>
                <p>Créez votre compte en 30 secondes</p>
            </div>

            <form id="registerForm" action="../src/actions/register_action.php" method="POST">
                <div class="input-group">
                    <input type="text" name="username" placeholder="Nom d'utilisateur" required>
                    <i class="fas fa-user input-icon"></i>
                </div>

                <div class="input-group">
                    <input type="email" name="email" placeholder="Adresse email" required>
                    <i class="fas fa-envelope input-icon"></i>
                </div>

                <div class="input-group">
                    <input type="password" name="password" placeholder="Mot de passe" required>
                    <i class="fas fa-lock input-icon"></i>
                </div>
                <p class="password-requirements">Minimum 8 caractères</p>

                <div class="input-group">
                    <select name="role">
                        <option value="collaborator">Collaborateur</option>
                        <option value="manager">Manager</option>
                    </select>
                    <i class="fas fa-user-tag input-icon"></i>
                </div>

                <button type="submit" class="register-button">S'inscrire</button>
            </form>

            <div class="login-link">
                Déjà un compte ? <a href="login.php">Connectez-vous</a>
            </div>
        </div>
    </div>

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/your-kit-code.js" crossorigin="anonymous"></script>

    <!-- Script de gestion du formulaire -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);

            try {
                const response = await axios.post(e.target.action, formData);
                if (response.data.success) {
                    window.location.href = 'login.php';
                } else {
                    alert(response.data.error || 'Erreur lors de l\'inscription');
                }
            } catch (error) {
                alert('Erreur serveur : Veuillez réessayer');
            }
        });
    </script>
</body>
</html>