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
}
