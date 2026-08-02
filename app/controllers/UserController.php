<?php
// ═══════════════════════════════════════════════
// CONTROLLER UserController
// Gère l'affichage des tableaux de bord, un par rôle.
// Chaque action vérifie le rôle avant d'afficher la vue (checkRole défini dans config/auth.php)
// ═══════════════════════════════════════════════

class UserController
{
    /**
     * Tableau de bord étudiant.
     * Seuls les utilisateurs ayant le rôle "student" peuvent y accéder.
     */
    public function studentDashboard()
    {
        checkRole('student');
        $this->renderDashboard('users/student_dashboard.php', 'Étudiant tableau de bord');
    }

    /**
     * Tableau de bord enseignant.
     * Seuls les utilisateurs ayant le rôle "teacher" peuvent y accéder.
     */
    public function teacherDashboard()
    {
        checkRole('teacher');
        $this->renderDashboard('users/teacher_dashboard.php', 'Enseignant tableau de bord');
    }

    /**
     * Tableau de bord du service logistique.
     * Seuls les utilisateurs ayant le rôle "logistics_department" peuvent y accéder.
     */
    public function logisticsDashboard()
    {
        checkRole('logistics_department');
        $this->renderDashboard('users/logistics_department_dashboard.php', 'Service logistique tableau de bord');
    }

    /**
     * Tableau de bord administrateur.
     * Seuls les utilisateurs ayant le rôle "admin" peuvent y accéder.
     */
    public function administratorDashboard()
    {
        checkRole('admin');
        $this->renderDashboard('users/administrator_dashboard.php', 'Administrateur tableau de bord');
    }

    /**
     * Inclut la vue d'un tableau de bord avec les variables qu'elle attend.
     */
    private function renderDashboard(string $view, string $pageTitle): void
    {
        $userName  = htmlspecialchars($_SESSION['user']['name']);
        $pageTitle = $pageTitle;

        require __DIR__ . '/../views/' . $view;
    }
}
