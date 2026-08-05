<?php
// ═══════════════════════════════════════════════
// CONTROLLER ReservationController
// Gère les réservations de salles (consultation, annulation, création, validation) selon le rôle.
// ═══════════════════════════════════════════════

require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../models/Room.php';

class ReservationController
{
    /**
     * Statuts autorisés pour le filtre (protège contre une valeur GET arbitraire).
     */
    private const ALLOWED_STATUSES = ['pending', 'approved', 'refused', 'cancelled'];

    /**
     * Affiche la liste des réservations de l'étudiant connecté.
     * Un filtre optionnel par statut est accepté via ?status=...
     */
    public function studentReservations()
    {
        checkRole('student');

        $userId   = (int) $_SESSION['user']['id'];
        $userName = htmlspecialchars($_SESSION['user']['name']);

        $status = $_GET['status'] ?? '';
        $status = in_array($status, self::ALLOWED_STATUSES, true) ? $status : '';

        $reservations = Reservation::findByUser($userId, $status);

        require __DIR__ . '/../views/reservations/student_reservation.php';
    }

    /**
     * Traite l'annulation d'une réservation par l'étudiant connecté.
     * (POST index.php?route=student/reservations/cancel)
     */
    public function cancelStudentReservation()
    {
        checkRole('student');

        $userId        = (int) $_SESSION['user']['id'];
        $reservationId = (int) ($_POST['id'] ?? 0);
        $status        = $_POST['status'] ?? '';

        if ($reservationId > 0) {
            Reservation::cancel($reservationId, $userId);
        }

        $redirect = 'index.php?route=student/reservations';

        if (in_array($status, self::ALLOWED_STATUSES, true)) {
            $redirect .= '&status=' . urlencode($status);
        }

        header('Location: ' . $redirect);
        exit;
    }

    /**
     * Affiche le formulaire permettant à l'étudiant connecté de faire
     * une nouvelle demande de réservation de salle.
     * (GET index.php?route=student/new-reservation)
     */
    public function newStudentReservationForm()
    {
        checkRole('student');

        $userName = htmlspecialchars($_SESSION['user']['name']);
        $rooms    = Room::findAllAvailable();

        // Ré-affiche les valeurs saisies précédemment en cas d'erreur de validation
        $old = $_SESSION['old'] ?? [];
        unset($_SESSION['old']);

        require __DIR__ . '/../views/reservations/student_make_reservation.php';
    }

    /**
     * Traite la demande de réservation soumise par l'étudiant connecté.
     * La réservation est créée avec le statut "pending" : elle doit être
     * validée par le service logistique avant de devenir effective.
     * (POST index.php?route=student/new-reservation/store)
     */
    public function storeStudentReservation()
    {
        checkRole('student');

        $userId  = (int) $_SESSION['user']['id'];
        $date    = trim($_POST['date'] ?? '');
        $from    = trim($_POST['de'] ?? '');
        $to      = trim($_POST['a'] ?? '');
        $roomId  = (int) ($_POST['salle'] ?? 0);
        $purpose = trim($_POST['motif'] ?? '');

        // Conserve la saisie pour pré-remplir le formulaire en cas d'erreur
        $old = [
            'date'  => $date,
            'de'    => $from,
            'a'     => $to,
            'salle' => $roomId,
            'motif' => $purpose,
        ];

        if ($date === '' || $from === '' || $to === '' || $roomId <= 0 || $purpose === '') {
            $_SESSION['error'] = "Veuillez remplir tous les champs et choisir une salle.";
            $_SESSION['old']   = $old;
            header('Location: index.php?route=student/new-reservation');
            exit;
        }

        // Les heures ne peuvent être choisies que parmi les créneaux fixes
        // proposés (07:00, 08:00, ..., 17:00) : pas de saisie libre type 07:40.
        if (!Reservation::isValidTimeBoundary($from) || !Reservation::isValidTimeBoundary($to)) {
            $_SESSION['error'] = "Veuillez choisir des heures parmi les créneaux proposés.";
            $_SESSION['old']   = $old;
            header('Location: index.php?route=student/new-reservation');
            exit;
        }

        $start = $date . ' ' . $from . ':00';
        $end   = $date . ' ' . $to . ':00';

        if (strtotime($start) === false || strtotime($end) === false || strtotime($end) <= strtotime($start)) {
            $_SESSION['error'] = "L'heure de fin doit être postérieure à l'heure de début.";
            $_SESSION['old']   = $old;
            header('Location: index.php?route=student/new-reservation');
            exit;
        }

        if (strtotime($start) < time()) {
            $_SESSION['error'] = "Impossible de réserver un créneau déjà passé.";
            $_SESSION['old']   = $old;
            header('Location: index.php?route=student/new-reservation');
            exit;
        }

        $room = Room::findAvailableById($roomId);

        if (!$room) {
            $_SESSION['error'] = "La salle sélectionnée n'est pas disponible.";
            $_SESSION['old']   = $old;
            header('Location: index.php?route=student/new-reservation');
            exit;
        }

        if (Reservation::hasConflict($roomId, $start, $end)) {
            $_SESSION['error'] = "Cette salle est déjà réservée sur ce créneau. Choisissez un autre créneau ou une autre salle.";
            $_SESSION['old']   = $old;
            header('Location: index.php?route=student/new-reservation');
            exit;
        }

        // Règle de gestion : une réservation d'étudiant attend toujours la validation du service logistique
        Reservation::create($roomId, $userId, $purpose, $start, $end, 'pending');

        $_SESSION['success'] = "Votre demande de réservation a bien été envoyée. Elle est en attente de validation.";

        header('Location: index.php?route=student/reservations');
        exit;
    }

    /**
     * Affiche la liste des réservations de l'enseignant connecté.
     * Un filtre optionnel par statut est accepté via ?status=...
     */
    public function teacherReservations()
    {
        checkRole('teacher');

        $userId   = (int) $_SESSION['user']['id'];
        $userName = htmlspecialchars($_SESSION['user']['name']);

        $status = $_GET['status'] ?? '';
        $status = in_array($status, self::ALLOWED_STATUSES, true) ? $status : '';

        $reservations = Reservation::findByUser($userId, $status);

        require __DIR__ . '/../views/reservations/teacher_reservation.php';
    }

    /**
     * Traite l'annulation d'une réservation par l'enseignant connecté.
     * (POST index.php?route=teacher/reservations/cancel)
     */
    public function cancelTeacherReservation()
    {
        checkRole('teacher');

        $userId        = (int) $_SESSION['user']['id'];
        $reservationId = (int) ($_POST['id'] ?? 0);
        $status        = $_POST['status'] ?? '';

        if ($reservationId > 0) {
            Reservation::cancel($reservationId, $userId);
        }

        $redirect = 'index.php?route=teacher/reservations';

        if (in_array($status, self::ALLOWED_STATUSES, true)) {
            $redirect .= '&status=' . urlencode($status);
        }

        header('Location: ' . $redirect);
        exit;
    }

    /**
     * Affiche le formulaire permettant à l'enseignant connecté de faire
     * une nouvelle demande de réservation de salle.
     * (GET index.php?route=teacher/new-reservation)
     */
    public function newTeacherReservationForm()
    {
        checkRole('teacher');

        $userName = htmlspecialchars($_SESSION['user']['name']);
        $rooms    = Room::findAllAvailable();

        // Ré-affiche les valeurs saisies précédemment en cas d'erreur de validation
        $old = $_SESSION['old'] ?? [];
        unset($_SESSION['old']);

        require __DIR__ . '/../views/reservations/teacher_make_reservation.php';
    }

    /**
     * Traite la demande de réservation soumise par l'enseignant connecté.
     * La réservation est créée avec le statut "pending" : elle doit être
     * validée par le service logistique avant de devenir effective.
     * (POST index.php?route=teacher/new-reservation/store)
     */
    public function storeTeacherReservation()
    {
        checkRole('teacher');

        $userId  = (int) $_SESSION['user']['id'];
        $date    = trim($_POST['date'] ?? '');
        $from    = trim($_POST['de'] ?? '');
        $to      = trim($_POST['a'] ?? '');
        $roomId  = (int) ($_POST['salle'] ?? 0);
        $purpose = trim($_POST['motif'] ?? '');

        // Conserve la saisie pour pré-remplir le formulaire en cas d'erreur
        $old = [
            'date'  => $date,
            'de'    => $from,
            'a'     => $to,
            'salle' => $roomId,
            'motif' => $purpose,
        ];

        if ($date === '' || $from === '' || $to === '' || $roomId <= 0 || $purpose === '') {
            $_SESSION['error'] = "Veuillez remplir tous les champs et choisir une salle.";
            $_SESSION['old']   = $old;
            header('Location: index.php?route=teacher/new-reservation');
            exit;
        }

        // Les heures ne peuvent être choisies que parmi les créneaux fixes
        // proposés (07:00, 08:00, ..., 17:00) : pas de saisie libre type 07:40.
        if (!Reservation::isValidTimeBoundary($from) || !Reservation::isValidTimeBoundary($to)) {
            $_SESSION['error'] = "Veuillez choisir des heures parmi les créneaux proposés.";
            $_SESSION['old']   = $old;
            header('Location: index.php?route=teacher/new-reservation');
            exit;
        }

        $start = $date . ' ' . $from . ':00';
        $end   = $date . ' ' . $to . ':00';

        if (strtotime($start) === false || strtotime($end) === false || strtotime($end) <= strtotime($start)) {
            $_SESSION['error'] = "L'heure de fin doit être postérieure à l'heure de début.";
            $_SESSION['old']   = $old;
            header('Location: index.php?route=teacher/new-reservation');
            exit;
        }

        if (strtotime($start) < time()) {
            $_SESSION['error'] = "Impossible de réserver un créneau déjà passé.";
            $_SESSION['old']   = $old;
            header('Location: index.php?route=teacher/new-reservation');
            exit;
        }

        $room = Room::findAvailableById($roomId);

        if (!$room) {
            $_SESSION['error'] = "La salle sélectionnée n'est pas disponible.";
            $_SESSION['old']   = $old;
            header('Location: index.php?route=teacher/new-reservation');
            exit;
        }

        if (Reservation::hasConflict($roomId, $start, $end)) {
            $_SESSION['error'] = "Cette salle est déjà réservée sur ce créneau. Choisissez un autre créneau ou une autre salle.";
            $_SESSION['old']   = $old;
            header('Location: index.php?route=teacher/new-reservation');
            exit;
        }

        Reservation::create($roomId, $userId, $purpose, $start, $end, 'pending');

        $_SESSION['success'] = "Votre demande de réservation a bien été envoyée. Elle est en attente de validation.";

        header('Location: index.php?route=teacher/reservations');
        exit;
    }

    /**
     * Affiche la liste des demandes de réservation en attente de validation.
     * Réservé au service logistique.
     * (GET index.php?route=logistics/requests)
     */
    public function logisticsRequests()
    {
        checkRole('logistics_department');

        $userName     = htmlspecialchars($_SESSION['user']['name']);
        $reservations = Reservation::findPending();

        require __DIR__ . '/../views/reservations/logistics_department_booking_requests.php';
    }

    /**
     * Valide une demande de réservation en attente.
     * (POST index.php?route=logistics/requests/approve)
     */
    public function approveReservation()
    {
        checkRole('logistics_department');

        $reservationId = (int) ($_POST['id'] ?? 0);
        $validatorId   = (int) $_SESSION['user']['id'];

        if ($reservationId > 0 && Reservation::approve($reservationId, $validatorId)) {
            $_SESSION['success'] = "La demande de réservation a bien été validée.";
        } else {
            $_SESSION['error'] = "Impossible de valider cette demande de réservation.";
        }

        header('Location: index.php?route=logistics/requests');
        exit;
    }

    /**
     * Refuse une demande de réservation en attente.
     * (POST index.php?route=logistics/requests/refuse)
     */
    public function refuseReservation()
    {
        checkRole('logistics_department');

        $reservationId = (int) ($_POST['id'] ?? 0);
        $validatorId   = (int) $_SESSION['user']['id'];
        $reason        = trim($_POST['motif_refus'] ?? '');
        $reason        = $reason !== '' ? $reason : null;

        if ($reservationId > 0 && Reservation::refuse($reservationId, $validatorId, $reason)) {
            $_SESSION['success'] = "La demande de réservation a bien été refusée.";
        } else {
            $_SESSION['error'] = "Impossible de refuser cette demande de réservation.";
        }

        header('Location: index.php?route=logistics/requests');
        exit;
    }

    /**
     * Affiche le calendrier des salles pour une journée donnée (aujourd'hui par
     * défaut), avec un filtre optionnel par salle. Réservé au service logistique.
     * Le calendrier est construit sur des créneaux fixes d'une heure
     * (07:00 - 08:00, 08:00 - 09:00, ..., 16:00 - 17:00).
     * (GET index.php?route=logistics/calendar)
     */
    public function roomSchedule()
    {
        checkRole('logistics_department');

        $userName = htmlspecialchars($_SESSION['user']['name']);

        // Date affichée : celle demandée en GET si elle est valide, sinon aujourd'hui.
        $date = trim($_GET['date'] ?? '');
        if (!$this->isValidDate($date)) {
            $date = date('Y-m-d');
        }

        // Filtre optionnel par salle.
        $roomId = (int) ($_GET['salle'] ?? 0);

        $rooms     = Room::findAllAvailable(); // liste complète, pour le <select> de filtre
        $timeSlots = Reservation::timeSlots();

        $activeReservations = Reservation::findActiveByDate($date, $roomId > 0 ? $roomId : null);

        // Construit une grille [id_room][heure_debut] = réservation, pour un
        // affichage simple dans la vue (une cellule par salle et par créneau).
        // Une réservation qui couvre plusieurs créneaux (ex: 07:00 - 09:00)
        // occupe bien chacune des cellules concernées (07:00-08:00 et 08:00-09:00).
        $scheduleGrid = [];

        foreach ($activeReservations as $reservation) {
            $resStart = date('H:i', strtotime($reservation['start_datetime']));
            $resEnd   = date('H:i', strtotime($reservation['end_datetime']));

            foreach ($timeSlots as $slot) {
                if ($slot['start'] >= $resStart && $slot['end'] <= $resEnd) {
                    $scheduleGrid[(int) $reservation['id_room']][$slot['start']] = $reservation;
                }
            }
        }

        // Lignes du tableau : toutes les salles, ou uniquement celle filtrée.
        $displayRooms = $roomId > 0
            ? array_values(array_filter($rooms, fn ($room) => (int) $room['id'] === $roomId))
            : $rooms;

        require __DIR__ . '/../views/reservations/logistics_department_room_schedule.php';
    }

    /**
     * Vérifie qu'une chaîne correspond bien à une date valide au format "Y-m-d".
     */
    private function isValidDate(string $date): bool
    {
        $parsed = DateTime::createFromFormat('Y-m-d', $date);

        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }
}