<?php
// ═══════════════════════════════════════════════
// CsvBuilder
// Utilitaire d'infrastructure (comme Database.php / Router.php) chargé
// uniquement de transformer des données tabulaires (en-têtes + lignes)
// en contenu CSV. Ne connaît ni la base de données, ni les rapports :
// respecte le principe de responsabilité unique (SRP).
// ═══════════════════════════════════════════════

class CsvBuilder
{
    /**
     * Construit le contenu d'un fichier CSV (encodage UTF-8 avec BOM pour
     * une ouverture correcte des caractères accentués dans Excel, séparateur
     * point-virgule car plus adapté aux locales françaises).
     *
     * @param string[]   $headers Intitulés de colonnes
     * @param string[][] $rows    Lignes de données (une ligne = tableau de cellules)
     */
    public static function build(array $headers, array $rows): string
    {
        $stream = fopen('php://temp', 'r+');

        // BOM UTF-8 : permet à Excel d'afficher correctement les accents.
        fwrite($stream, "\xEF\xBB\xBF");

        fputcsv($stream, $headers, ';');

        foreach ($rows as $row) {
            fputcsv($stream, $row, ';');
        }

        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);

        return $content;
    }
}
