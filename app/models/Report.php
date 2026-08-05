<?php
// ═══════════════════════════════════════════════
// MODEL Report
// Gère uniquement l'accès aux données nécessaires aux rapports
// administrateur (taux d'occupation, nombre de réservations par salle,
// statistiques par utilisateur) ainsi que la persistance de l'historique
// des exports (table `reports`).
// Respecte le principe de responsabilité unique (SRP) : ce modèle ne
// s'occupe pas de la session (Session.php), ni des utilisateurs
// (User.php), ni de la mise en forme des fichiers CSV/PDF (app/core).
// ═══════════════════════════════════════════════

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/Reservation.php';
require_once __DIR__ . '/User.php';

class Report
{
    /**
     * Types de rapport proposés à l'administrateur.
     */
    public const TYPES = ['taux_occupation', 'nb_reservations', 'stats_utilisateurs'];

    /**
     * Formats d'export proposés à l'administrateur.
     */
    public const FORMATS = ['pdf', 'csv'];

    /**
     * Libellés lisibles (français) des types de rapport.
     */
    private const TYPE_LABELS = [
        'taux_occupation'    => "Taux d'occupation des salles",
        'nb_reservations'    => 'Nombre de réservations par salle',
        'stats_utilisateurs' => 'Statistiques par utilisateur',
    ];

    /**
     * Convertit un type de rapport technique en libellé lisible.
     */
    public static function typeLabel(string $type): string
    {
        return self::TYPE_LABELS[$type] ?? $type;
    }

    /**
     * Indique si un type de rapport est valide.
     */
    public static function isValidType(string $type): bool
    {
        return in_array($type, self::TYPES, true);
    }

    /**
     * Indique si un format d'export est valide.
     */
    public static function isValidFormat(string $format): bool
    {
        return in_array($format, self::FORMATS, true);
    }

    /**
     * Construit les données (en-têtes + lignes) d'un rapport pour la
     * période [$periodStart ; $periodEnd] (bornes incluses, format "Y-m-d").
     * Retourne un tableau ['headers' => [...], 'rows' => [[...], ...]].
     */
    public static function build(string $type, string $periodStart, string $periodEnd): array
    {
        return match ($type) {
            'taux_occupation'    => self::occupancyByRoom($periodStart, $periodEnd),
            'nb_reservations'    => self::reservationsByRoom($periodStart, $periodEnd),
            'stats_utilisateurs' => self::statsByUser($periodStart, $periodEnd),
            default              => ['headers' => [], 'rows' => []],
        };
    }

    /**
     * Rapport "Taux d'occupation des salles" : pour chaque salle, le nombre
     * d'heures réservées (réservations validées uniquement) rapporté au
     * nombre d'heures d'ouverture disponibles sur la période.
     */
    private static function occupancyByRoom(string $periodStart, string $periodEnd): array
    {
        $db = Database::connect();

        $start    = $periodStart . ' 00:00:00';
        $endExcl  = date('Y-m-d', strtotime($periodEnd . ' +1 day')) . ' 00:00:00';
        $nbDays   = max(1, (int) round((strtotime($endExcl) - strtotime($start)) / 86400));

        // Nombre d'heures d'ouverture par jour, déduit des créneaux fixes
        // définis dans le modèle Reservation (07:00 → 17:00 = 10 heures).
        $hoursPerDay     = count(Reservation::TIME_BOUNDARIES) - 1;
        $availableHours  = $nbDays * $hoursPerDay;

        $sql = "SELECT
                    r.id,
                    r.name,
                    COALESCE(SUM(
                        GREATEST(0, TIMESTAMPDIFF(
                            SECOND,
                            GREATEST(res.start_datetime, ?),
                            LEAST(res.end_datetime, ?)
                        ))
                    ), 0) / 3600 AS reserved_hours
                FROM rooms r
                LEFT JOIN reservations res
                    ON res.id_room = r.id
                    AND res.status = 'approved'
                    AND res.start_datetime < ?
                    AND res.end_datetime > ?
                GROUP BY r.id, r.name
                ORDER BY r.name ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute([$start, $endExcl, $endExcl, $start]);
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $rows = [];
        foreach ($rooms as $room) {
            $reservedHours = round((float) $room['reserved_hours'], 1);
            $rate          = $availableHours > 0 ? round(($reservedHours / $availableHours) * 100, 1) : 0.0;

            $rows[] = [
                $room['name'],
                number_format($reservedHours, 1, ',', ' '),
                (string) $availableHours,
                number_format($rate, 1, ',', ' ') . ' %',
            ];
        }

        return [
            'headers' => ['Salle', 'Heures réservées', 'Heures disponibles', "Taux d'occupation"],
            'rows'    => $rows,
        ];
    }

    /**
     * Rapport "Nombre de réservations par salle" : pour chaque salle, le
     * détail du nombre de réservations par statut sur la période (basé
     * sur la date de début de la réservation).
     */
    private static function reservationsByRoom(string $periodStart, string $periodEnd): array
    {
        $db = Database::connect();

        $start   = $periodStart . ' 00:00:00';
        $endExcl = date('Y-m-d', strtotime($periodEnd . ' +1 day')) . ' 00:00:00';

        $sql = "SELECT
                    r.name,
                    COUNT(res.id) AS total,
                    SUM(res.status = 'pending')   AS nb_pending,
                    SUM(res.status = 'approved')  AS nb_approved,
                    SUM(res.status = 'refused')   AS nb_refused,
                    SUM(res.status = 'cancelled') AS nb_cancelled
                FROM rooms r
                LEFT JOIN reservations res
                    ON res.id_room = r.id
                    AND res.start_datetime >= ?
                    AND res.start_datetime < ?
                GROUP BY r.id, r.name
                ORDER BY total DESC, r.name ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute([$start, $endExcl]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $rows = [];
        foreach ($data as $room) {
            $rows[] = [
                $room['name'],
                (string) (int) $room['total'],
                (string) (int) $room['nb_pending'],
                (string) (int) $room['nb_approved'],
                (string) (int) $room['nb_refused'],
                (string) (int) $room['nb_cancelled'],
            ];
        }

        return [
            'headers' => ['Salle', 'Total', 'En attente', 'Validées', 'Refusées', 'Annulées'],
            'rows'    => $rows,
        ];
    }

    /**
     * Rapport "Statistiques par utilisateur" : pour chaque étudiant ou
     * enseignant ayant fait au moins une réservation sur la période, le
     * détail du nombre de réservations par statut.
     */
    private static function statsByUser(string $periodStart, string $periodEnd): array
    {
        $db = Database::connect();

        $start   = $periodStart . ' 00:00:00';
        $endExcl = date('Y-m-d', strtotime($periodEnd . ' +1 day')) . ' 00:00:00';

        $sql = "SELECT
                    u.name,
                    u.role,
                    COUNT(res.id) AS total,
                    SUM(res.status = 'pending')   AS nb_pending,
                    SUM(res.status = 'approved')  AS nb_approved,
                    SUM(res.status = 'refused')   AS nb_refused,
                    SUM(res.status = 'cancelled') AS nb_cancelled
                FROM users u
                INNER JOIN reservations res
                    ON res.id_user = u.id
                    AND res.start_datetime >= ?
                    AND res.start_datetime < ?
                WHERE u.role IN ('student', 'teacher')
                GROUP BY u.id, u.name, u.role
                ORDER BY total DESC, u.name ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute([$start, $endExcl]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $rows = [];
        foreach ($data as $user) {
            $rows[] = [
                $user['name'],
                User::roleLabel($user['role']),
                (string) (int) $user['total'],
                (string) (int) $user['nb_pending'],
                (string) (int) $user['nb_approved'],
                (string) (int) $user['nb_refused'],
                (string) (int) $user['nb_cancelled'],
            ];
        }

        return [
            'headers' => ['Utilisateur', 'Rôle', 'Total', 'En attente', 'Validées', 'Refusées', 'Annulées'],
            'rows'    => $rows,
        ];
    }

    /**
     * Enregistre l'historique d'un export (table `reports`) et retourne
     * l'id généré.
     */
    public static function log(string $title, string $format, string $filePath, int $generatedBy): int
    {
        $db = Database::connect();

        $stmt = $db->prepare(
            "INSERT INTO reports (title, type, file_path, generated_by) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$title, $format, $filePath, $generatedBy]);

        return (int) $db->lastInsertId();
    }

    /**
     * Récupère les derniers rapports générés, avec le nom de
     * l'administrateur qui les a exportés. Utilisé pour afficher
     * l'historique des exports sur la page "Rapports".
     */
    public static function findRecent(int $limit = 10): array
    {
        $db = Database::connect();

        $limit = max(1, $limit);

        $sql = "SELECT
                    rep.id,
                    rep.title,
                    rep.type,
                    rep.file_path,
                    rep.generated_at,
                    u.name AS generated_by_name
                FROM reports rep
                LEFT JOIN users u ON u.id = rep.generated_by
                ORDER BY rep.generated_at DESC
                LIMIT $limit";

        $stmt = $db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recherche un rapport déjà généré par son id. Utilisé pour permettre
     * à l'administrateur de re-télécharger un export depuis l'historique.
     */
    public static function findById(int $id)
    {
        $db = Database::connect();

        $stmt = $db->prepare("SELECT * FROM reports WHERE id = ?");
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
