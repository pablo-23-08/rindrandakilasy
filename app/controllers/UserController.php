<?php
// ═══════════════════════════════════════════════
// CONTROLLER UserController
// Gère l'affichage des tableaux de bord (un par rôle) ainsi que la page
// "Mon profil" (consultation + modification des informations personnelles
// et du mot de passe). La gestion de session reste dans Session.php,
// jamais dans ce contrôleur ni dans le model User.
// ═══════════════════════════════════════════════

require_once __DIR__ . '/../models/User.php';

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

    /**
     * Affiche la page "Mon profil" de l'étudiant connecté.
     * (GET index.php?route=student/profile)
     */
    public function studentProfile()
    {
        checkRole('student');

        $userId = (int) $_SESSION['user']['id'];
        $user   = User::findById($userId);

        if (!$user) {
            // Sécurité : l'utilisateur en session n'existe plus en base
            header('Location: index.php?route=logout');
            exit;
        }

        $userName = htmlspecialchars($_SESSION['user']['name']);

        require __DIR__ . '/../views/users/student_profile.php';
    }

    /**
     * Traite la modification du profil (nom, email, mot de passe) de
     * l'étudiant connecté.
     * (POST index.php?route=student/profile/update)
     */
    public function updateStudentProfile()
    {
        checkRole('student');

        $userId = (int) $_SESSION['user']['id'];

        $name            = trim($_POST['nom'] ?? '');
        $email           = trim($_POST['email'] ?? '');
        $newPassword     = trim($_POST['new_password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');

        if ($name === '' || $email === '') {
            $_SESSION['error'] = "Veuillez remplir le nom complet et l'adresse email.";
            header('Location: index.php?route=student/profile');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "L'adresse email saisie n'est pas valide.";
            header('Location: index.php?route=student/profile');
            exit;
        }

        if (User::findByEmailExcludingId($email, $userId)) {
            $_SESSION['error'] = "Cette adresse email est déjà utilisée par un autre compte.";
            header('Location: index.php?route=student/profile');
            exit;
        }

        // La modification du mot de passe est facultative : on ne la traite
        // que si l'un des deux champs a été rempli.
        if ($newPassword !== '' || $confirmPassword !== '') {
            if (strlen($newPassword) < 8) {
                $_SESSION['error'] = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
                header('Location: index.php?route=student/profile');
                exit;
            }

            if ($newPassword !== $confirmPassword) {
                $_SESSION['error'] = "Les deux mots de passe ne correspondent pas.";
                header('Location: index.php?route=student/profile');
                exit;
            }
        }

        User::updateProfile($userId, $name, $email);

        if ($newPassword !== '') {
            User::updatePassword($userId, password_hash($newPassword, PASSWORD_DEFAULT));
        }

        // Met à jour le nom affiché dans la session PHP courante
        $_SESSION['user']['name'] = $name;

        $_SESSION['success'] = "Votre profil a bien été mis à jour.";

        header('Location: index.php?route=student/profile');
        exit;
    }
}
