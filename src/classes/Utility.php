<?php
/**
 * Classe Utility
 *
 * Cette classe fournit des fonctions utilitaires utilisées dans toute l'application.
 * Elle contient uniquement des méthodes statiques qui peuvent être appelées directement
 * sans avoir besoin d'instancier la classe.
 *
 * Exemples d'utilisation :
 * - Formatage de dates
 * - Affichage de texte
 * - Redirection HTTP
 * - Réponses JSON
 * - Validation de formulaires
 */
class Utility {
    /**
     * Formate une date pour l'affichage
     *
     * Cette méthode prend une chaîne de date et la formate selon le format spécifié.
     * Si la date est null, elle retourne une chaîne vide.
     *
     * @param string|null $dateString La chaîne de date à formater (peut être null)
     * @param string $format Le format de date désiré (par défaut: 'd/m/Y H:i')
     * @return string La date formatée ou une chaîne vide si null
     *
     * Exemple d'utilisation :
     * echo Utility::formatDate('2023-01-15 14:30:00'); // Affiche "15/01/2023 14:30"
     */
    public static function formatDate(?string $dateString, string $format = 'd/m/Y H:i'): string {
        // Si la date n'est pas null, la formater selon le format spécifié, sinon retourner une chaîne vide
        return $dateString ? date($format, strtotime($dateString)) : '';
    }

    /**
     * Affiche du texte sans échappement
     *
     * Cette méthode est un raccourci pour afficher du texte.
     * Dans cette version simplifiée, elle n'effectue pas d'échappement HTML.
     *
     * @param string|null $string La chaîne à afficher (peut être null)
     * @return string La chaîne ou une chaîne vide si null
     *
     * Exemple d'utilisation :
     * echo Utility::h($username); // Affiche le nom d'utilisateur
     */
    public static function h(?string $string): string {
        // Retourne la chaîne ou une chaîne vide si null
        // L'opérateur ?? est l'opérateur de fusion null (null coalescing)
        return $string ?? '';
    }

    /**
     * Redirige vers une URL
     *
     * Cette méthode redirige l'utilisateur vers l'URL spécifiée et arrête l'exécution du script.
     * Elle est utile pour les redirections après une action (connexion, déconnexion, etc.).
     *
     * @param string $url L'URL vers laquelle rediriger
     * @return void
     *
     * Exemple d'utilisation :
     * Utility::redirect('login.php'); // Redirige vers la page de connexion
     */
    public static function redirect(string $url): void {
        // Envoie un en-tête de redirection HTTP
        header("Location: {$url}");
        // Arrête l'exécution du script pour éviter tout traitement supplémentaire
        exit();
    }

    /**
     * Envoie une réponse au format JSON
     *
     * Cette méthode définit l'en-tête Content-Type à application/json,
     * encode les données en JSON et arrête l'exécution du script.
     * Elle est utile pour les requêtes AJAX.
     *
     * @param array $data Les données à encoder en JSON
     * @return void
     *
     * Exemple d'utilisation :
     * Utility::jsonResponse(['success' => true, 'message' => 'Opération réussie']);
     */
    public static function jsonResponse(array $data): void {
        // Définit l'en-tête Content-Type à application/json
        header('Content-Type: application/json');
        // Encode les données en JSON et les envoie
        echo json_encode($data);
        // Arrête l'exécution du script
        exit();
    }

    /**
     * Valide les champs obligatoires d'un formulaire
     *
     * Cette méthode vérifie si tous les champs obligatoires sont présents et non vides.
     * Elle est utile pour valider les formulaires avant de traiter les données.
     *
     * @param array $data Les données à valider (généralement $_POST)
     * @param array $requiredFields Les champs obligatoires à vérifier
     * @return array Le résultat de la validation avec deux clés :
     *               - 'valid' : bool (true si tous les champs sont valides)
     *               - 'errors' : array (liste des messages d'erreur)
     *
     * Exemple d'utilisation :
     * $validation = Utility::validateRequired($_POST, ['username', 'password']);
     * if (!$validation['valid']) {
     *     // Afficher les erreurs
     * }
     */
    public static function validateRequired(array $data, array $requiredFields): array {
        // Initialise un tableau pour stocker les erreurs
        $errors = [];

        // Vérifie chaque champ obligatoire
        foreach ($requiredFields as $field) {
            // Si le champ n'existe pas ou est vide, ajoute une erreur
            if (!isset($data[$field]) || $data[$field] === '') {
                $errors[] = "Le champ '{$field}' est obligatoire";
            }
        }

        // Retourne le résultat de la validation
        return [
            'valid' => empty($errors),  // True si aucune erreur, false sinon
            'errors' => $errors         // Liste des erreurs
        ];
    }
}
