<?php
// ═══════════════════════════════════════════════
// PdfBuilder
// Utilitaire d'infrastructure (comme Database.php / Router.php) chargé
// uniquement de générer un document PDF simple (titre + tableau de
// données), en PHP natif, sans aucune librairie externe.
// Ne connaît ni la base de données, ni les rapports : respecte le
// principe de responsabilité unique (SRP).
// ═══════════════════════════════════════════════

class PdfBuilder
{
    private const PAGE_WIDTH   = 595.28; // A4 portrait, en points
    private const PAGE_HEIGHT  = 841.89;
    private const MARGIN       = 40;
    private const TITLE_SIZE   = 16;
    private const META_SIZE    = 10;
    private const BODY_SIZE    = 9;
    private const LINE_HEIGHT  = 14;

    /**
     * Génère le contenu binaire d'un fichier PDF à partir d'un titre, de
     * quelques lignes d'information (période, date de génération, ...)
     * et d'un tableau (en-têtes + lignes) déjà formaté en colonnes
     * alignées (police à chasse fixe Courier).
     *
     * @param string   $title    Titre du rapport
     * @param string[] $metaLines Lignes d'information affichées sous le titre
     * @param string[] $headerLine Ligne d'en-tête du tableau (déjà alignée)
     * @param string[] $rowLines   Lignes de données du tableau (déjà alignées)
     */
    public static function build(string $title, array $metaLines, string $headerLine, array $rowLines): string
    {
        $topY         = self::PAGE_HEIGHT - self::MARGIN;
        $bodyBottomY  = self::MARGIN + 20;

        // Découpage des lignes du tableau en pages (l'en-tête du tableau
        // est répété en haut de chaque page pour la lisibilité).
        $availableForFirstPage = ($topY - 60 - count($metaLines) * self::LINE_HEIGHT - 20 - self::LINE_HEIGHT - $bodyBottomY);
        $availableForNextPages = ($topY - 60 - self::LINE_HEIGHT - $bodyBottomY);

        $linesPerFirstPage = max(5, (int) floor($availableForFirstPage / self::LINE_HEIGHT));
        $linesPerNextPage  = max(5, (int) floor($availableForNextPages / self::LINE_HEIGHT));

        $pagesOfRows = [];
        if (empty($rowLines)) {
            $pagesOfRows[] = [];
        } else {
            $remaining = $rowLines;
            $pagesOfRows[] = array_splice($remaining, 0, $linesPerFirstPage);
            while (!empty($remaining)) {
                $pagesOfRows[] = array_splice($remaining, 0, $linesPerNextPage);
            }
        }

        $totalPages = count($pagesOfRows);
        $objects    = [];

        // 1: Catalogue -- 2: Pages -- 3: Police titre/méta -- 4: Police tableau
        $objects[1] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $objects[3] = "3 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>\nendobj\n";
        $objects[4] = "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Courier /Encoding /WinAnsiEncoding >>\nendobj\n";

        $pageObjNums = [];
        $nextObjNum  = 5;

        foreach ($pagesOfRows as $pageIndex => $pageRows) {
            $pageObjNum    = $nextObjNum++;
            $contentObjNum = $nextObjNum++;
            $pageObjNums[] = $pageObjNum;

            $stream = self::buildPageContent($title, $metaLines, $headerLine, $pageRows, $pageIndex + 1, $totalPages);

            $objects[$pageObjNum] = "$pageObjNum 0 obj\n"
                . "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 " . self::PAGE_WIDTH . ' ' . self::PAGE_HEIGHT . "] "
                . "/Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents $contentObjNum 0 R >>\n"
                . "endobj\n";

            $objects[$contentObjNum] = "$contentObjNum 0 obj\n"
                . '<< /Length ' . strlen($stream) . " >>\n"
                . "stream\n" . $stream . "\nendstream\nendobj\n";
        }

        $kids = implode(' ', array_map(fn ($n) => "$n 0 R", $pageObjNums));
        $objects[2] = "2 0 obj\n<< /Type /Pages /Kids [$kids] /Count " . count($pageObjNums) . " >>\nendobj\n";

        ksort($objects);

        // Assemblage final avec calcul des offsets pour la table xref.
        $pdf    = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [];

        foreach ($objects as $num => $body) {
            $offsets[$num] = strlen($pdf);
            $pdf .= $body;
        }

        $xrefStart = strlen($pdf);
        $maxNum    = max(array_keys($objects));

        $pdf .= "xref\n0 " . ($maxNum + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($n = 1; $n <= $maxNum; $n++) {
            if (isset($offsets[$n])) {
                $pdf .= sprintf("%010d 00000 n \n", $offsets[$n]);
            } else {
                $pdf .= "0000000000 00000 f \n";
            }
        }

        $pdf .= "trailer\n<< /Size " . ($maxNum + 1) . " /Root 1 0 R >>\nstartxref\n$xrefStart\n%%EOF";

        return $pdf;
    }

    /**
     * Construit le flux de contenu (texte à afficher) d'une page.
     */
    private static function buildPageContent(
        string $title,
        array $metaLines,
        string $headerLine,
        array $rowLines,
        int $pageNumber,
        int $totalPages
    ): string {
        $topY = self::PAGE_HEIGHT - self::MARGIN;
        $x    = self::MARGIN;

        $parts   = [];
        $parts[] = 'BT';
        $parts[] = '/F1 ' . self::TITLE_SIZE . ' Tf';
        $parts[] = "$x " . round($topY - self::TITLE_SIZE) . ' Td';
        $parts[] = '(' . self::encodeText($title) . ') Tj';
        $parts[] = 'ET';

        $currentY = $topY - self::TITLE_SIZE - 16;

        if (!empty($metaLines)) {
            $parts[] = 'BT';
            $parts[] = '/F2 ' . self::META_SIZE . ' Tf';
            $parts[] = "$x " . round($currentY) . ' Td';
            $parts[] = '(' . self::encodeText($metaLines[0]) . ') Tj';
            for ($i = 1, $c = count($metaLines); $i < $c; $i++) {
                $parts[] = '0 -' . self::LINE_HEIGHT . ' Td';
                $parts[] = '(' . self::encodeText($metaLines[$i]) . ') Tj';
            }
            $parts[] = 'ET';
            $currentY -= self::LINE_HEIGHT * count($metaLines) + 12;
        }

        $parts[] = 'BT';
        $parts[] = '/F2 ' . self::BODY_SIZE . ' Tf';
        $parts[] = "$x " . round($currentY) . ' Td';
        $parts[] = '(' . self::encodeText($headerLine) . ') Tj';

        foreach ($rowLines as $line) {
            $parts[] = '0 -' . self::LINE_HEIGHT . ' Td';
            $parts[] = '(' . self::encodeText($line) . ') Tj';
        }
        $parts[] = 'ET';

        $parts[] = 'BT';
        $parts[] = '/F2 8 Tf';
        $parts[] = "$x " . (self::MARGIN - 15) . ' Td';
        $parts[] = '(' . self::encodeText("Page $pageNumber / $totalPages") . ') Tj';
        $parts[] = 'ET';

        return implode("\n", $parts);
    }

    /**
     * Convertit un texte UTF-8 en Windows-1252 (encodage attendu par les
     * polices standard PDF via /WinAnsiEncoding, seul moyen d'afficher
     * correctement les caractères accentués français sans police
     * personnalisée) puis échappe les caractères réservés PDF.
     */
    private static function encodeText(string $text): string
    {
        $converted = @iconv('UTF-8', 'CP1252//TRANSLIT//IGNORE', $text);

        if ($converted === false) {
            $converted = $text;
        }

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $converted);
    }
}
