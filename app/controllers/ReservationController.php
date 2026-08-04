<?php
// ═══════════════════════════════════════════════
// CONTROLLER ReservationController
// Gère les réservations de salles (consultation, annulation...) selon le rôle.
// ═══════════════════════════════════════════════

require_once __DIR__ . '/../models/Reservation.php';

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
}
