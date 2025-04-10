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
    <title>Inscription - Task Manager</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Réutilisation des styles de la page login avec ajustements */
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }

        .login-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            transform: translateY(0);
            transition: transform 0.3s ease;
        }

        .login-box:hover {
            transform: translateY(-5px);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #2d3748;
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #718096;
        }

        .input-group {
            margin-bottom: 25px;
            position: relative;
        }

        .input-group input {
            width: 90%;
            padding: 12px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .input-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .input-group input::placeholder {
            color: #a0aec0;
        }

        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
        }

        .register-button {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .register-button:hover {
            background: #764ba2;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(116, 76, 162, 0.3);
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            color: #718096;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: #764ba2;
        }

        /* Animation */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-box {
            animation: slideIn 0.6s ease-out;
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            .login-box {
                padding: 25px;
                margin: 15px;
            }
        }

        /* Ajout spécifique à la page register */
        .password-requirements {
            color: #718096;
            font-size: 0.9em;
            margin: -10px 0 15px 0;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>Commencez maintenant</h1>
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