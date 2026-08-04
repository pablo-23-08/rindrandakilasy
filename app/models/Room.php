<?php
// ═══════════════════════════════════════════════
// MODEL Room
// Gère uniquement l'accès aux données des salles (table `rooms`) et de
// leurs équipements (tables `equipments` / `room_equipments`).
// Respecte le principe de responsabilité unique (SRP) : ce modèle ne
// s'occupe pas des réservations, ni des utilisateurs, ni des sessions.
// ═══════════════════════════════════════════════

require_once __DIR__ . '/../core/Database.php';

class Room
{
    /**
     * Récupère toutes les salles actuellement disponibles (status = 'available'),
     * avec la liste de leurs équipements agrégée en une seule chaîne lisible.
     * Utilisé pour proposer le choix d'une salle lors d'une réservation.
     */
    public static function findAllAvailable(): array
    {
        $db = Database::connect();

        $sql = "SELECT
                    r.id,
                    r.name,
                    r.capacity,
                    r.description,
                    GROUP_CONCAT(e.name ORDER BY e.name SEPARATOR ', ') AS equipments
                FROM rooms r
                LEFT JOIN room_equipments re ON re.id_room = r.id
                LEFT JOIN equipments e ON e.id = re.id_equipment
                WHERE r.status = 'available'
                GROUP BY r.id, r.name, r.capacity, r.description
                ORDER BY r.name";

        $stmt = $db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recherche une salle disponible par son id (status = 'available').
     * Sert de garde-fou avant de créer une réservation.
     * Retourne false si la salle n'existe pas ou n'est pas disponible.
     */
    public static function findAvailableById(int $id)
    {
        $db = Database::connect();

        $stmt = $db->prepare("SELECT * FROM rooms WHERE id = ? AND status = 'available'");
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
