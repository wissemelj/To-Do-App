<?php
/**
 * export_tasks.php - Exporte les tâches au format PDF
 *
 * Ce fichier génère un document PDF contenant les tâches de l'utilisateur.
 * Utilise la bibliothèque TCPDF pour la génération du PDF.
 *
 * Paramètres:
 * - status: Filtre optionnel par statut (todo, in_progress, done)
 */

// Configuration et vérification de l'authentification
require_once __DIR__ . '/../includes/config.php';

if (!$userObj->isLoggedIn()) {
    header('HTTP/1.1 401 Unauthorized');
    exit('Accès non autorisé');
}

// Récupération des paramètres
$statusFilter = $_GET['status'] ?? null;

// Récupération des données
$userId = $userObj->getLoggedInUserId();
$username = $_SESSION['username'] ?? 'Utilisateur';
$tasks = $taskObj->getTasksForExport($userId, $statusFilter);

// Vérification des tâches
if (empty($tasks)) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<p>Aucune tâche à exporter. <a href="' . SITE_URL . '/index.php">Retour au tableau de bord</a></p>';
    exit();
}

// Préparation des données communes
$statusLabels = Task::getStatusLabels();

// Comptage des tâches par statut
$tasksByStatus = [];
foreach ($tasks as $task) {
    $status = $task['status'];
    if (!isset($tasksByStatus[$status])) {
        $tasksByStatus[$status] = 0;
    }
    $tasksByStatus[$status]++;
}

// Vérification de la bibliothèque TCPDF
$possible_paths = [
    __DIR__ . '/../../lib/tcpdf/tcpdf.php',
    __DIR__ . '/../../lib/TCPDF-main/tcpdf.php',
    __DIR__ . '/../../lib/tcpdf-main/tcpdf.php',
    __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php'
];

$tcpdf_path = null;
foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        $tcpdf_path = $path;
        break;
    }
}

if (!$tcpdf_path) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9;">';
    echo '<h2 style="color: #e74c3c;">Bibliothèque TCPDF introuvable</h2>';
    echo '<p>La bibliothèque TCPDF est nécessaire pour générer des PDF mais n\'a pas été trouvée.</p>';
    echo '<p>Vérifiez que vous avez bien installé TCPDF dans l\'un des emplacements suivants :</p>';
    echo '<ul>';
    foreach ($possible_paths as $path) {
        echo '<li><code>' . htmlspecialchars(str_replace(__DIR__ . '/../..', '', $path)) . '</code></li>';
    }
    echo '</ul>';
    echo '<p>Instructions d\'installation :</p>';
    echo '<ol>';
    echo '<li>Téléchargez TCPDF depuis <a href="https://github.com/tecnickcom/TCPDF/releases" target="_blank">GitHub</a></li>';
    echo '<li>Extrayez le contenu du fichier ZIP dans le dossier <code>lib/tcpdf</code></li>';
    echo '<li>Assurez-vous que le fichier <code>tcpdf.php</code> est présent dans ce dossier</li>';
    echo '</ol>';
    echo '<p><a href="' . SITE_URL . '/index.php" style="color: #3498db; text-decoration: none;">Retour au tableau de bord</a></p>';
    echo '</div>';
    exit();
}

// Chargement de la bibliothèque TCPDF
require_once $tcpdf_path;

// Classe personnalisée pour le PDF
class MYPDF extends TCPDF {
    // En-tête de page
    public function Header() {
        // Couleurs plus froides
        $primary_color = array(66, 153, 225); // #4299e1 (bleu ciel)

        // Fond de l'en-tête
        $this->SetFillColor($primary_color[0], $primary_color[1], $primary_color[2]);
        $this->Rect(0, 0, $this->getPageWidth(), 25, 'F');

        // Effet de dégradé léger (barre plus foncée en bas)
        $darker_blue = array(49, 130, 206); // #3182ce (bleu plus foncé)
        $this->SetFillColor($darker_blue[0], $darker_blue[1], $darker_blue[2]);
        $this->Rect(0, 20, $this->getPageWidth(), 5, 'F');

        // Logo et titre
        $this->SetY(8);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('helvetica', 'B', 20);
        $this->Cell(0, 8, 'TacTâche', 0, false, 'C');

        // Sous-titre
        $this->SetY(16);
        $this->SetFont('helvetica', '', 8);
        $this->Cell(0, 4, 'Exporté le ' . date('d/m/Y à H:i'), 0, false, 'C');

        // Espace après l'en-tête - réduit pour un design plus compact
        $this->SetY(30);
    }

    // Pied de page
    public function Footer() {
        // Couleurs plus froides
        $primary_color = array(66, 153, 225); // #4299e1 (bleu ciel)

        $this->SetY(-20);

        // Ligne de séparation
        $this->SetDrawColor($primary_color[0], $primary_color[1], $primary_color[2]);
        $this->SetLineWidth(0.5);
        $this->Line(15, $this->GetY(), $this->getPageWidth() - 15, $this->GetY());

        // Numéro de page
        $this->SetY(-15);
        $this->SetFont('helvetica', '', 8);
        $this->SetTextColor(113, 128, 150); // #718096 (gris bleuté)
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C');
    }

    // Fonction pour créer une carte de tâche (version simplifiée)
    public function TaskCard($task, $statusLabels, $statusColors, $y = null) {
        // $statusLabels est passé mais non utilisé dans cette version simplifiée
        if ($y !== null) {
            $this->SetY($y);
        }

        $startY = $this->GetY();
        $pageWidth = $this->getPageWidth();
        $margin = 15;
        $cardWidth = $pageWidth - (2 * $margin);
        $cardHeight = 18; // Hauteur encore plus réduite pour un design plus simple

        // Fond de la carte (blanc pour plus de simplicité)
        $this->SetFillColor(255, 255, 255);
        $this->RoundedRect($margin, $startY, $cardWidth, $cardHeight, 2, '1111', 'F');

        // Statut de la tâche (indiqué par une couleur)
        $statusKey = $task['status'];
        if (isset($statusColors[$statusKey])) {
            $color = $statusColors[$statusKey];
            $this->SetFillColor($color[0], $color[1], $color[2]);
        } else {
            $this->SetFillColor(200, 200, 200);
        }

        // Petit cercle coloré pour indiquer le statut
        $this->Ellipse($margin + 5, $startY + $cardHeight/2, 2.5, 2.5, 0, 0, 360, 'F');

        // Statut de la tâche (pour le cercle coloré)
        $statusKey = $task['status'];

        // Titre de la tâche
        $this->SetFont('helvetica', 'B', 9);
        $this->SetTextColor(45, 55, 72); // #2d3748
        $this->SetXY($margin + 12, $startY + 4);

        // Tronquer le titre s'il est trop long
        $title = $task['title'];
        if (strlen($title) > 60) {
            $title = substr($title, 0, 57) . '...';
        }
        $this->Cell($cardWidth * 0.6, 5, $title, 0, 0);

        // Date d'échéance (à droite)
        $this->SetFont('helvetica', '', 8);
        $this->SetTextColor(74, 85, 104); // #4a5568 (gris bleuté plus foncé)
        $dueDate = $task['due_date'] ? date('d/m/Y', strtotime($task['due_date'])) : '-';
        $dueDateWidth = $this->GetStringWidth('Échéance: ' . $dueDate);
        $this->SetXY($margin + $cardWidth - $dueDateWidth - 8, $startY + 4);
        $this->Cell($dueDateWidth, 5, 'Échéance: ' . $dueDate, 0, 0);

        // Assigné à (en bas à gauche)
        $this->SetFont('helvetica', '', 8);
        $this->SetTextColor(113, 128, 150); // #718096 (gris bleuté)
        $assignedTo = $task['assigned_username'] ?? '-';
        $this->SetXY($margin + 12, $startY + 10);
        $this->Cell($cardWidth * 0.5, 4, 'Assigné à: ' . $assignedTo, 0, 0);

        return $startY + $cardHeight + 4; // Retourne la position Y pour la prochaine carte avec un petit espacement
    }


}

// Création du document PDF
$pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Utiliser la police par défaut

// Métadonnées du document
$pdf->SetCreator('TacTâche');
$pdf->SetAuthor($username);
$pdf->SetTitle('Mes tâches - TacTâche');
$pdf->SetSubject('Liste des tâches');

// Configuration de la page
$pdf->SetMargins(15, 15, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(true, 25);
$pdf->AddPage();

// Couleurs plus froides
$primary_color = array(66, 153, 225); // #4299e1 (bleu ciel)
$secondary_color = array(49, 130, 206); // #3182ce (bleu plus foncé)

// Carte d'informations (version compacte)
$margin = 15;
$pageWidth = $pdf->getPageWidth();
$cardWidth = $pageWidth - (2 * $margin);

// Fond de la carte d'informations
$pdf->SetFillColor(247, 250, 252); // #f7fafc (gris très clair bleuté)
$pdf->RoundedRect($margin, $pdf->GetY(), $cardWidth, 25, 2, '1111', 'F');

// Bordure gauche colorée
$pdf->SetFillColor($primary_color[0], $primary_color[1], $primary_color[2]);
$pdf->Rect($margin, $pdf->GetY(), 3, 25, 'F');

// Informations utilisateur
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(45, 55, 72); // #2d3748
$pdf->SetXY($margin + 6, $pdf->GetY() + 4);
$pdf->Cell(25, 5, 'Utilisateur:', 0, 0);
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(60, 5, $username, 0, 0);

// Total des tâches
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetXY($margin + 6, $pdf->GetY() + 8);
$pdf->Cell(25, 5, 'Total:', 0, 0);
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(20, 5, count($tasks) . ' tâches', 0, 0);

// Filtre (si applicable)
if ($statusFilter) {
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetXY($margin + 100, $pdf->GetY());
    $pdf->Cell(25, 5, 'Filtre:', 0, 0);
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(50, 5, $statusLabels[$statusFilter] ?? $statusFilter, 0, 0);
}

// Date d'exportation
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetXY($margin + 100, $pdf->GetY() - 8);
$pdf->Cell(25, 5, 'Exporté le:', 0, 0);
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(50, 5, date('d/m/Y'), 0, 0);

$pdf->Ln(10);

// Couleurs des statuts (couleurs reconnaissables)
$statusColors = [
    'todo' => [66, 153, 225],       // Bleu (#4299e1) - À Faire
    'in_progress' => [237, 137, 54], // Orange (#ed8936) - En Cours
    'done' => [72, 187, 120]        // Vert (#48bb78) - Terminé
];

// Pas d'emojis

// Pas de titre pour la section des tâches
$pdf->Ln(5);

// Utilisation de notre méthode TaskCard pour afficher chaque tâche
$nextY = $pdf->GetY();
foreach ($tasks as $task) {
    // Vérifier si on a besoin d'une nouvelle page
    if ($nextY > $pdf->getPageHeight() - 40) {
        $pdf->AddPage();
        $nextY = $pdf->GetY();
    }

    // Afficher la carte de tâche
    $nextY = $pdf->TaskCard($task, $statusLabels, $statusColors, $nextY);
}

// Section résumé (version très simplifiée)
// Vérifier s'il reste assez d'espace sur la page actuelle
if ($pdf->GetY() > $pdf->getPageHeight() - 40) {
    $pdf->AddPage();
} else {
    $pdf->Ln(10);
}

// Ligne de séparation
$pdf->SetDrawColor(226, 232, 240); // #e2e8f0 (gris très clair)
$pdf->SetLineWidth(0.5);
$pdf->Line($margin, $pdf->GetY(), $pageWidth - $margin, $pdf->GetY());
$pdf->Ln(5);

// Titre de la section
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor(45, 55, 72); // #2d3748
$pdf->Cell(0, 6, 'Résumé des tâches', 0, 1, 'L');
$pdf->Ln(2);

// Créer une ligne simple pour chaque statut
foreach ($statusLabels as $statusKey => $statusLabel) {
    $count = $tasksByStatus[$statusKey] ?? 0;
    if ($count == 0) continue; // Ne pas afficher les statuts sans tâches

    $percentage = count($tasks) > 0 ? round(($count / count($tasks)) * 100) : 0;

    // Couleur du statut
    if (isset($statusColors[$statusKey])) {
        $color = $statusColors[$statusKey];
    } else {
        $color = array(200, 200, 200); // Gris par défaut
    }

    // Petit cercle coloré pour indiquer le statut
    $pdf->SetFillColor($color[0], $color[1], $color[2]);
    $circleY = $pdf->GetY() + 3;
    $pdf->Ellipse($margin + 4, $circleY, 2.5, 2.5, 0, 0, 360, 'F');

    // Nom du statut
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(45, 55, 72); // #2d3748
    $pdf->SetX($margin + 10);
    $pdf->Cell(50, 6, $statusLabel, 0, 0);

    // Nombre de tâches
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(20, 6, $count . ' tâches', 0, 0);

    // Pourcentage
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(113, 128, 150); // #718096
    $pdf->Cell(20, 6, '(' . $percentage . '%)', 0, 1);

    $pdf->Ln(1);
}

// Restaurer les paramètres
$pdf->SetLineWidth(0.2);
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(5);

// Génération du PDF avec nom de fichier automatique
$filename = htmlspecialchars($username) . '_' . date('Ymd') . '.pdf';
$pdf->Output($filename, 'D');
