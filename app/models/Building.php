<?php
// ═══════════════════════════════════════════════
// MODEL Building
// Gère uniquement l'accès aux données des bâtiments (table `buildings`).
// Respecte le principe de responsabilité unique (SRP) : ce modèle ne
// s'occupe pas des salles, des équipements, ni d'aucune autre entité.
// Séparé de Room.php afin que chaque modèle ne gère qu'une seule table
// (même logique que la séparation Session.php / User.php).
// ═══════════════════════════════════════════════

require_once __DIR__ . '/../core/Database.php';

class Building
{
    /**
     * Récupère tous les bâtiments, triés par nom.
     * Utilisé pour le <select> "Bâtiment" du formulaire d'ajout/modification
     * d'une salle, dans l'espace de gestion des salles (administrateur).
     */
    public static function findAll(): array
    {
        $db = Database::connect();

        $stmt = $db->query("SELECT id, name FROM buildings ORDER BY name");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
