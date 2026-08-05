<?php
// ═══════════════════════════════════════════════
// CONTROLLER ReportController
// Gère la page "Rapports" de l'administrateur : affichage du formulaire
// d'export et génération du fichier (CSV ou PDF) à partir des données
// fournies par le model Report.
// La gestion de session (Session.php) et des utilisateurs (User.php) ne
// sont pas de la responsabilité de ce contrôleur (principe de
// responsabilité unique) : il ne s'occupe que des rapports (Report.php)
// et délègue la mise en forme des fichiers à app/core/CsvBuilder.php et
// app/core/PdfBuilder.php.
// ═══════════════════════════════════════════════

require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../core/CsvBuilder.php';
require_once __DIR__ . '/../core/PdfBuilder.php';

class ReportController
{
    /**
     * Dossier (hors du dossier public/) dans lequel sont sauvegardés les
     * fichiers de rapports générés, afin de garder un historique des
     * exports (table `reports`) sans les rendre directement accessibles
     * par une URL publique.
     */
    private const STORAGE_DIR = __DIR__ . '/../../storage/reports';

    /**
     * Affiche le formulaire de génération de rapport ainsi que
     * l'historique des derniers exports.
     * (GET index.php?route=admin/reports)
     */
    public function index()
    {
        checkRole('admin');

        $userName = htmlspecialchars($_SESSION['user']['name']);

        // Ré-affiche les valeurs saisies précédemment en cas d'erreur de validation
        $old = $_SESSION['old'] ?? [];
        unset($_SESSION['old']);

        $recentReports = Report::findRecent(10);

        require __DIR__ . '/../views/users/administrator_reports.php';
    }

    /**
     * Traite le formulaire d'export : valide les champs, construit les
     * données du rapport demandé, génère le fichier (CSV ou PDF),
     * l'enregistre dans l'historique puis le propose au téléchargement.
     * (POST index.php?route=admin/reports/export)
     */
    public function export()
    {
        checkRole('admin');

        $type       = trim($_POST['type_rapport'] ?? '');
        $dateDebut  = trim($_POST['date_debut'] ?? '');
        $dateFin    = trim($_POST['date_fin'] ?? '');
        $format     = trim($_POST['format'] ?? '');

        $old = [
            'type_rapport' => $type,
            'date_debut'   => $dateDebut,
            'date_fin'     => $dateFin,
            'format'       => $format,
        ];

        if (!Report::isValidType($type)) {
            $_SESSION['error'] = 'Veuillez choisir un type de rapport valide.';
            $_SESSION['old']   = $old;
            header('Location: index.php?route=admin/reports');
            exit;
        }

        if (!Report::isValidFormat($format)) {
            $_SESSION['error'] = "Veuillez choisir un format d'export valide.";
            $_SESSION['old']   = $old;
            header('Location: index.php?route=admin/reports');
            exit;
        }

        if (($dateDebut !== '' && !$this->isValidDate($dateDebut)) || ($dateFin !== '' && !$this->isValidDate($dateFin))) {
            $_SESSION['error'] = 'Les dates saisies ne sont pas valides.';
            $_SESSION['old']   = $old;
            header('Location: index.php?route=admin/reports');
            exit;
        }

        if ($dateDebut !== '' && $dateFin !== '' && $dateDebut > $dateFin) {
            $_SESSION['error'] = "La date de début doit être antérieure ou égale à la date de fin.";
            $_SESSION['old']   = $old;
            header('Location: index.php?route=admin/reports');
            exit;
        }

        // Période par défaut : du premier jour du mois en cours à aujourd'hui,
        // si l'administrateur n'a pas précisé de dates.
        $periodStart = $dateDebut !== '' ? $dateDebut : date('Y-m-01');
        $periodEnd   = $dateFin !== '' ? $dateFin : date('Y-m-d');

        $report = Report::build($type, $periodStart, $periodEnd);

        $reportTitle = Report::typeLabel($type) . ' du ' . $this->formatDateFr($periodStart) . ' au ' . $this->formatDateFr($periodEnd);

        $generatedBy = (int) $_SESSION['user']['id'];
        $timestamp   = date('Y-m-d_His');
        $extension   = $format === 'csv' ? 'csv' : 'pdf';
        $fileName    = 'rapport_' . $type . '_' . $timestamp . '.' . $extension;

        if (!is_dir(self::STORAGE_DIR)) {
            mkdir(self::STORAGE_DIR, 0775, true);
        }

        if ($format === 'csv') {
            $content     = CsvBuilder::build($report['headers'], $report['rows']);
            $contentType = 'text/csv; charset=UTF-8';
        } else {
            [$headerLine, $rowLines] = $this->alignColumns($report['headers'], $report['rows']);

            $metaLines = [
                'Type de rapport : ' . Report::typeLabel($type),
                'Période : du ' . $this->formatDateFr($periodStart) . ' au ' . $this->formatDateFr($periodEnd),
                'Généré le ' . $this->formatDateFr(date('Y-m-d')) . ' à ' . date('H:i') . ' par ' . $_SESSION['user']['name'],
            ];

            $content     = PdfBuilder::build(Report::typeLabel($type), $metaLines, $headerLine, $rowLines);
            $contentType = 'application/pdf';
        }

        $filePath = self::STORAGE_DIR . '/' . $fileName;
        file_put_contents($filePath, $content);

        Report::log($reportTitle, $format, 'storage/reports/' . $fileName, $generatedBy);

        // Propose directement le fichier au téléchargement.
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: no-store, no-cache');
        echo $content;
        exit;
    }

    /**
     * Permet de re-télécharger un rapport déjà généré depuis l'historique.
     * (GET index.php?route=admin/reports/download&id=...)
     */
    public function download()
    {
        checkRole('admin');

        $id     = (int) ($_GET['id'] ?? 0);
        $report = Report::findById($id);

        if (!$report) {
            $_SESSION['error'] = 'Rapport introuvable.';
            header('Location: index.php?route=admin/reports');
            exit;
        }

        $filePath = __DIR__ . '/../../' . $report['file_path'];

        if (!is_file($filePath)) {
            $_SESSION['error'] = "Le fichier de ce rapport n'est plus disponible.";
            header('Location: index.php?route=admin/reports');
            exit;
        }

        $contentType = $report['type'] === 'csv' ? 'text/csv; charset=UTF-8' : 'application/pdf';

        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-store, no-cache');
        readfile($filePath);
        exit;
    }

    /**
     * Aligne les cellules d'un tableau en colonnes de largeur fixe (police
     * à chasse fixe) pour un rendu lisible dans le PDF généré par
     * PdfBuilder. Retourne [ligne d'en-tête, lignes de données].
     */
    private function alignColumns(array $headers, array $rows): array
    {
        $widths = array_map('strlen', $headers);

        foreach ($rows as $row) {
            foreach ($row as $i => $cell) {
                $widths[$i] = max($widths[$i] ?? 0, strlen((string) $cell));
            }
        }

        $pad = fn (array $cells) => implode('  ', array_map(
            fn ($cell, $i) => str_pad((string) $cell, $widths[$i]),
            $cells,
            array_keys($cells)
        ));

        $headerLine = $pad($headers);
        $rowLines   = array_map($pad, $rows);

        return [$headerLine, $rowLines];
    }

    /**
     * Vérifie qu'une chaîne correspond bien à une date valide au format "Y-m-d".
     */
    private function isValidDate(string $date): bool
    {
        $parsed = DateTime::createFromFormat('Y-m-d', $date);

        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }

    /**
     * Formate une date "Y-m-d" au format lisible "jj/mm/aaaa".
     */
    private function formatDateFr(string $date): string
    {
        $parsed = DateTime::createFromFormat('Y-m-d', $date);

        return $parsed ? $parsed->format('d/m/Y') : $date;
    }
}
