<?php
// ═══════════════════════════════════════════════
// MODEL Room
// Gère uniquement l'accès aux données des salles (table `rooms`) et de
// leurs équipements (tables `equipments` / `room_equipments`).
// Respecte le principe de responsabilité unique (SRP) : ce modèle ne
// s'occupe pas des réservations, ni des utilisateurs, ni des sessions,
// ni des bâtiments (table gérée par le modèle Building.php).
// ═══════════════════════════════════════════════

require_once __DIR__ . '/../core/Database.php';

class Room
{
    /**
     * Statuts techniques valides en base de données pour une salle.
     */
    public const ALLOWED_STATUSES = ['available', 'maintenance', 'disabled'];

    /**
     * Libellé général affiché à l'utilisateur : une salle est soit
     * "Opérationnelle" (available), soit "Inopérationnelle" (maintenance
     * ou disabled).
     */
    private const STATUS_LABELS = [
        'available'   => 'Opérationnelle',
        'maintenance' => 'Inopérationnelle',
        'disabled'    => 'Inopérationnelle',
    ];

    /**
     * Libellé détaillé du statut technique, affiché en complément dans
     * l'espace d'administration des salles (ex : "En maintenance").
     */
    private const STATUS_DETAIL_LABELS = [
        'available'   => 'Disponible',
        'maintenance' => 'En maintenance',
        'disabled'    => 'Désactivée',
    ];

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

    /**
     * Récupère toutes les salles, quel que soit leur statut.
     * Utilisé pour le filtre "salle" de l'historique du service logistique :
     * une réservation passée peut concerner une salle aujourd'hui en
     * maintenance ou désactivée, elle doit donc rester sélectionnable.
     */
    public static function findAll(): array
    {
        $db = Database::connect();

        $stmt = $db->query("SELECT id, name, capacity, description, status FROM rooms ORDER BY name");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Convertit un statut technique ('available' | 'maintenance' | 'disabled')
     * en libellé général lisible ("Opérationnelle" / "Inopérationnelle").
     */
    public static function statusLabel(string $status): string
    {
        return self::STATUS_LABELS[$status] ?? $status;
    }

    /**
     * Convertit un statut technique en libellé détaillé lisible
     * ("Disponible" / "En maintenance" / "Désactivée").
     */
    public static function statusDetailLabel(string $status): string
    {
        return self::STATUS_DETAIL_LABELS[$status] ?? $status;
    }

    /**
     * Récupère toutes les salles pour l'espace d'administration, avec :
     *   - la liste de leurs équipements agrégée en une chaîne lisible ;
     *   - leur nombre d'utilisations (= nombre de réservations validées).
     * Filtres optionnels :
     *   - $status : 'available' (opérationnelle) | 'unavailable' (inopérationnelle) | null (toutes)
     *   - $search : recherche libre sur le nom de la salle
     * Réservé au contrôleur RoomController (rôle admin).
     */
    public static function findAllForAdmin(?string $status = null, ?string $search = null): array
    {
        $db = Database::connect();

        $conditions = [];
        $params     = [];

        if ($status === 'available') {
            $conditions[] = "r.status = 'available'";
        } elseif ($status === 'unavailable') {
            $conditions[] = "r.status IN ('maintenance', 'disabled')";
        }

        if ($search !== null && $search !== '') {
            $conditions[] = "r.name LIKE ?";
            $params[]     = '%' . $search . '%';
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "SELECT
                    r.id,
                    r.building_id,
                    r.name,
                    r.capacity,
                    r.description,
                    r.status,
                    GROUP_CONCAT(DISTINCT e.name ORDER BY e.name SEPARATOR ', ') AS equipments,
                    (
                        SELECT COUNT(*) FROM reservations res
                        WHERE res.id_room = r.id AND res.status = 'approved'
                    ) AS usage_count
                FROM rooms r
                LEFT JOIN room_equipments re ON re.id_room = r.id
                LEFT JOIN equipments e ON e.id = re.id_equipment
                $where
                GROUP BY r.id, r.building_id, r.name, r.capacity, r.description, r.status
                ORDER BY r.name";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recherche une salle par son id avec la liste de ses équipements,
     * pour pré-remplir le formulaire de modification (espace administrateur).
     * Retourne false si la salle n'existe pas.
     */
    public static function findByIdForAdmin(int $id)
    {
        $db = Database::connect();

        $stmt = $db->prepare(
            "SELECT
                r.*,
                GROUP_CONCAT(DISTINCT e.name ORDER BY e.name SEPARATOR ', ') AS equipments
             FROM rooms r
             LEFT JOIN room_equipments re ON re.id_room = r.id
             LEFT JOIN equipments e ON e.id = re.id_equipment
             WHERE r.id = ?
             GROUP BY r.id"
        );
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crée une nouvelle salle ainsi que ses équipements associés.
     * Les équipements qui n'existent pas encore sont créés à la volée
     * dans la table `equipments`. Retourne l'id de la salle créée.
     */
    public static function create(
        int $buildingId,
        string $name,
        int $capacity,
        ?string $description,
        string $status,
        array $equipmentNames
    ): int {
        $db = Database::connect();

        $stmt = $db->prepare(
            "INSERT INTO rooms (building_id, name, capacity, description, status) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$buildingId, $name, $capacity, $description, $status]);

        $roomId = (int) $db->lastInsertId();

        self::syncEquipments($db, $roomId, $equipmentNames);

        return $roomId;
    }

    /**
     * Met à jour une salle existante ainsi que la liste complète de ses
     * équipements (les équipements non listés sont retirés de la salle).
     */
    public static function update(
        int $id,
        int $buildingId,
        string $name,
        int $capacity,
        ?string $description,
        string $status,
        array $equipmentNames
    ): bool {
        $db = Database::connect();

        $stmt = $db->prepare(
            "UPDATE rooms SET building_id = ?, name = ?, capacity = ?, description = ?, status = ? WHERE id = ?"
        );
        $result = $stmt->execute([$buildingId, $name, $capacity, $description, $status, $id]);

        self::syncEquipments($db, $id, $equipmentNames);

        return $result;
    }

    /**
     * Remplace la liste des équipements d'une salle : crée en base les
     * équipements qui n'existent pas encore (table `equipments`), puis
     * reconstruit entièrement la table de liaison `room_equipments`
     * pour cette salle.
     */
    private static function syncEquipments(PDO $db, int $roomId, array $equipmentNames): void
    {
        $equipmentIds = [];

        foreach ($equipmentNames as $name) {
            $name = trim($name);

            if ($name === '') {
                continue;
            }

            $stmt = $db->prepare("SELECT id FROM equipments WHERE name = ?");
            $stmt->execute([$name]);
            $equipment = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($equipment) {
                $equipmentIds[] = (int) $equipment['id'];
                continue;
            }

            $stmt = $db->prepare("INSERT INTO equipments (name) VALUES (?)");
            $stmt->execute([$name]);
            $equipmentIds[] = (int) $db->lastInsertId();
        }

        $db->prepare("DELETE FROM room_equipments WHERE id_room = ?")->execute([$roomId]);

        if (!empty($equipmentIds)) {
            $stmt = $db->prepare("INSERT IGNORE INTO room_equipments (id_room, id_equipment) VALUES (?, ?)");

            foreach ($equipmentIds as $equipmentId) {
                $stmt->execute([$roomId, $equipmentId]);
            }
        }
    }
}
