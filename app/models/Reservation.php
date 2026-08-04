<?php
// ═══════════════════════════════════════════════
// MODEL Reservation
// Gère uniquement l'accès aux données des réservations (table `reservations`).
// Respecte le principe de responsabilité unique (SRP) : ce modèle ne
// s'occupe que des réservations, pas des salles, ni des utilisateurs, ni des sessions.
// ═══════════════════════════════════════════════

require_once __DIR__ . '/../core/Database.php';

class Reservation
{
    /**
     * Statuts pour lesquels une réservation peut encore être annulée par son auteur.
     */
    private const CANCELLABLE_STATUSES = ['pending', 'approved'];

    /**
     * Statuts qui occupent réellement un créneau : une salle ayant déjà une
     * réservation "pending" ou "approved" sur un créneau ne peut pas être
     * re-réservée sur ce même créneau (règle de gestion n°1).
     */
    private const BLOCKING_STATUSES = ['pending', 'approved'];

    /**
     * Libellés lisibles (français) des statuts stockés en base.
     */
    private const STATUS_LABELS = [
        'pending'   => 'Attente',
        'approved'  => 'Validé',
        'refused'   => 'Refusé',
        'cancelled' => 'Annulé',
    ];

    /**
     * Récupère toutes les réservations d'un utilisateur (avec le nom de la salle),
     * triées de la plus récente à la plus ancienne.
     * Un filtre optionnel sur le statut peut être appliqué.
     */
    public static function findByUser(int $userId, ?string $status = null): array
    {
        $db = Database::connect();

        $sql = "SELECT
                    r.id,
                    r.start_datetime,
                    r.end_datetime,
                    r.status,
                    rm.name AS room_name
                FROM reservations r
                INNER JOIN rooms rm ON rm.id = r.id_room
                WHERE r.id_user = ?";

        $params = [$userId];

        if (!empty($status)) {
            $sql .= " AND r.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY r.start_datetime DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recherche une réservation par son id, à condition qu'elle appartienne
     * bien à l'utilisateur donné. Sert de garde-fou avant toute action (ex: annulation).
     */
    public static function findByIdAndUser(int $id, int $userId)
    {
        $db = Database::connect();

        $stmt = $db->prepare("SELECT * FROM reservations WHERE id = ? AND id_user = ?");
        $stmt->execute([$id, $userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Annule une réservation appartenant à l'utilisateur donné, uniquement
     * si elle est encore "en attente" ou déjà "validée".
     * Retourne true si l'annulation a bien été effectuée.
     */
    public static function cancel(int $id, int $userId): bool
    {
        $reservation = self::findByIdAndUser($id, $userId);

        if (!$reservation || !in_array($reservation['status'], self::CANCELLABLE_STATUSES, true)) {
            return false;
        }

        $db = Database::connect();

        $stmt = $db->prepare(
            "UPDATE reservations SET status = 'cancelled' WHERE id = ? AND id_user = ?"
        );
        $stmt->execute([$id, $userId]);

        return true;
    }

    /**
     * Indique si une réservation peut encore être annulée par son auteur.
     */
    public static function isCancellable(string $status): bool
    {
        return in_array($status, self::CANCELLABLE_STATUSES, true);
    }

    /**
     * Convertit un statut technique (base de données) en libellé lisible.
     */
    public static function statusLabel(string $status): string
    {
        return self::STATUS_LABELS[$status] ?? $status;
    }

    /**
     * Vérifie si une salle a déjà une réservation ("pending" ou "approved")
     * qui chevauche le créneau donné.
     * Deux créneaux se chevauchent si : début A < fin B ET fin A > début B.
     */
    public static function hasConflict(int $roomId, string $start, string $end): bool
    {
        $db = Database::connect();

        $placeholders = implode(',', array_fill(0, count(self::BLOCKING_STATUSES), '?'));

        $sql = "SELECT COUNT(*) FROM reservations
                WHERE id_room = ?
                  AND status IN ($placeholders)
                  AND start_datetime < ?
                  AND end_datetime > ?";

        $params = array_merge([$roomId], self::BLOCKING_STATUSES, [$end, $start]);

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Récupère toutes les réservations en attente ("pending"), avec le nom
     * du demandeur et le nom de la salle, triées par créneau le plus proche.
     * Utilisé par le service logistique pour traiter les demandes.
     */
    public static function findPending(): array
    {
        $db = Database::connect();

        $sql = "SELECT
                    r.id,
                    r.purpose,
                    r.start_datetime,
                    r.end_datetime,
                    r.status,
                    u.name AS requester_name,
                    rm.name AS room_name
                FROM reservations r
                INNER JOIN users u ON u.id = r.id_user
                INNER JOIN rooms rm ON rm.id = r.id_room
                WHERE r.status = 'pending'
                ORDER BY r.start_datetime ASC";

        $stmt = $db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Valide une demande de réservation encore "en attente".
     * Enregistre qui l'a validée et à quel moment.
     * Retourne true si une ligne a bien été mise à jour.
     */
    public static function approve(int $id, int $validatorId): bool
    {
        $db = Database::connect();

        $stmt = $db->prepare(
            "UPDATE reservations
             SET status = 'approved', validated_at = NOW(), validated_by = ?
             WHERE id = ? AND status = 'pending'"
        );
        $stmt->execute([$validatorId, $id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Refuse une demande de réservation encore "en attente".
     * Enregistre qui l'a refusée, à quel moment, et le motif du refus (facultatif).
     * Retourne true si une ligne a bien été mise à jour.
     */
    public static function refuse(int $id, int $validatorId, ?string $reason = null): bool
    {
        $db = Database::connect();

        $stmt = $db->prepare(
            "UPDATE reservations
             SET status = 'refused', validated_at = NOW(), validated_by = ?, refusal_reason = ?
             WHERE id = ? AND status = 'pending'"
        );
        $stmt->execute([$validatorId, $reason, $id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Crée une nouvelle réservation et retourne son id.
     * Les réservations créées par un étudiant sont, par défaut, mises en
     * statut "pending" : elles doivent être validées par le service logistique.
     */
    public static function create(
        int $roomId,
        int $userId,
        string $purpose,
        string $start,
        string $end,
        string $status = 'pending'
    ): int {
        $db = Database::connect();

        $stmt = $db->prepare(
            "INSERT INTO reservations (id_room, id_user, purpose, start_datetime, end_datetime, status)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$roomId, $userId, $purpose, $start, $end, $status]);

        return (int) $db->lastInsertId();
    }
}