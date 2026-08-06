<?php
// ═══════════════════════════════════════════════
// CONTROLLER UserController
// Gère l'affichage des tableaux de bord (un par rôle) ainsi que la page
// "Mon profil", commune à TOUS les rôles (student, teacher,
// logistics_department, admin) : une seule action profile()/updateProfile()
// et une seule vue (users/profile.php) au lieu d'une paire par rôle.
// La gestion de session reste dans Session.php, jamais dans ce contrôleur
// ni dans le model User (principe de responsabilité unique).
// ═══════════════════════════════════════════════

require_once __DIR__ . '/../models/User.php';

class UserController
{
    /**
     * Associe chaque rôle à la route de sa page "Mon profil".
     * Utilisé pour rediriger vers la bonne page après une mise à jour.
     */
    private const PROFILE_ROUTES = [
        'student'               => 'student/profile',
        'teacher'                => 'teacher/profile',
        'logistics_department'   => 'logistics/profile',
        'admin'                  => 'administrator/profile',
    ];

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
     * Affiche la page "Gestion des utilisateurs" de l'administrateur :
     * liste des étudiants et enseignants avec leurs statistiques de
     * réservations, avec filtre par rôle et recherche par nom.
     * (GET index.php?route=administrator/users)
     */
    public function manageUsers()
    {
        checkRole('admin');

        $roleFilter = trim($_GET['role'] ?? '');
        $search     = trim($_GET['search'] ?? '');

        $users = User::findManageableWithReservationStats(
            $roleFilter !== '' ? $roleFilter : null,
            $search !== '' ? $search : null
        );

        $userName = htmlspecialchars($_SESSION['user']['name']);

        require __DIR__ . '/../views/users/administrator_manage_users.php';
    }

    /**
     * Affiche le formulaire d'ajout d'un nouvel utilisateur (étudiant ou
     * enseignant).
     * (GET index.php?route=administrator/users/new)
     */
    public function newUserForm()
    {
        checkRole('admin');

        $userName = htmlspecialchars($_SESSION['user']['name']);
        $editUser = null; // null = mode création

        // Ré-affiche les valeurs saisies précédemment en cas d'erreur de validation
        $old = $_SESSION['old'] ?? [];
        unset($_SESSION['old']);

        require __DIR__ . '/../views/users/administrator_user_form.php';
    }

    /**
     * Traite la création d'un nouvel utilisateur (étudiant ou enseignant)
     * par l'administrateur.
     * (POST index.php?route=administrator/users/store)
     */
    public function storeUser()
    {
        checkRole('admin');

        $name             = trim($_POST['nom'] ?? '');
        $email            = trim($_POST['email'] ?? '');
        $role             = trim($_POST['role'] ?? '');
        $password         = trim($_POST['password'] ?? '');
        $confirmPassword  = trim($_POST['confirm_password'] ?? '');

        // Conserve la saisie pour pré-remplir le formulaire en cas d'erreur
        $old = ['nom' => $name, 'email' => $email, 'role' => $role];

        if ($name === '' || $email === '' || $role === '' || $password === '') {
            $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
            $_SESSION['old']   = $old;
            header('Location: index.php?route=administrator/users/new');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "L'adresse email saisie n'est pas valide.";
            $_SESSION['old']   = $old;
            header('Location: index.php?route=administrator/users/new');
            exit;
        }

        if (!User::isManageableRole($role)) {
            $_SESSION['error'] = "Le rôle sélectionné n'est pas valide.";
            $_SESSION['old']   = $old;
            header('Location: index.php?route=administrator/users/new');
            exit;
        }

        if (strlen($password) < 8) {
            $_SESSION['error'] = "Le mot de passe doit contenir au moins 8 caractères.";
            $_SESSION['old']   = $old;
            header('Location: index.php?route=administrator/users/new');
            exit;
        }

        if ($password !== $confirmPassword) {
            $_SESSION['error'] = "Les deux mots de passe ne correspondent pas.";
            $_SESSION['old']   = $old;
            header('Location: index.php?route=administrator/users/new');
            exit;
        }

        if (User::findByEmail($email)) {
            $_SESSION['error'] = "Cette adresse email est déjà utilisée par un autre compte.";
            $_SESSION['old']   = $old;
            header('Location: index.php?route=administrator/users/new');
            exit;
        }

        User::create($name, $email, password_hash($password, PASSWORD_DEFAULT), $role);

        $_SESSION['success'] = "L'utilisateur a bien été créé.";
        header('Location: index.php?route=administrator/users');
        exit;
    }

    /**
     * Affiche le formulaire de modification d'un utilisateur existant
     * (étudiant ou enseignant).
     * (GET index.php?route=administrator/users/edit&id=...)
     */
    public function editUserForm()
    {
        checkRole('admin');

        $id       = (int) ($_GET['id'] ?? 0);
        $editUser = User::findById($id);

        if (!$editUser || !User::isManageableRole($editUser['role'])) {
            $_SESSION['error'] = "Utilisateur introuvable.";
            header('Location: index.php?route=administrator/users');
            exit;
        }

        $userName = htmlspecialchars($_SESSION['user']['name']);

        // Ré-affiche les valeurs saisies précédemment en cas d'erreur de validation
        $old = $_SESSION['old'] ?? [];
        unset($_SESSION['old']);

        require __DIR__ . '/../views/users/administrator_user_form.php';
    }

    /**
     * Traite la modification d'un utilisateur existant (étudiant ou
     * enseignant) par l'administrateur : nom, email, rôle et,
     * facultativement, mot de passe.
     * (POST index.php?route=administrator/users/update)
     */
    public function updateUser()
    {
        checkRole('admin');

        $id       = (int) ($_POST['id'] ?? 0);
        $editUser = User::findById($id);

        if (!$editUser || !User::isManageableRole($editUser['role'])) {
            $_SESSION['error'] = "Utilisateur introuvable.";
            header('Location: index.php?route=administrator/users');
            exit;
        }

        $name            = trim($_POST['nom'] ?? '');
        $email           = trim($_POST['email'] ?? '');
        $role            = trim($_POST['role'] ?? '');
        $newPassword     = trim($_POST['password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');

        $old          = ['nom' => $name, 'email' => $email, 'role' => $role];
        $redirectBack = 'index.php?route=administrator/users/edit&id=' . $id;

        if ($name === '' || $email === '' || $role === '') {
            $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
            $_SESSION['old']   = $old;
            header('Location: ' . $redirectBack);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "L'adresse email saisie n'est pas valide.";
            $_SESSION['old']   = $old;
            header('Location: ' . $redirectBack);
            exit;
        }

        if (!User::isManageableRole($role)) {
            $_SESSION['error'] = "Le rôle sélectionné n'est pas valide.";
            $_SESSION['old']   = $old;
            header('Location: ' . $redirectBack);
            exit;
        }

        if (User::findByEmailExcludingId($email, $id)) {
            $_SESSION['error'] = "Cette adresse email est déjà utilisée par un autre compte.";
            $_SESSION['old']   = $old;
            header('Location: ' . $redirectBack);
            exit;
        }

        // La modification du mot de passe est facultative
        if ($newPassword !== '' || $confirmPassword !== '') {
            if (strlen($newPassword) < 8) {
                $_SESSION['error'] = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
                $_SESSION['old']   = $old;
                header('Location: ' . $redirectBack);
                exit;
            }

            if ($newPassword !== $confirmPassword) {
                $_SESSION['error'] = "Les deux mots de passe ne correspondent pas.";
                $_SESSION['old']   = $old;
                header('Location: ' . $redirectBack);
                exit;
            }
        }

        User::updateByAdmin($id, $name, $email, $role);

        if ($newPassword !== '') {
            User::updatePassword($id, password_hash($newPassword, PASSWORD_DEFAULT));
        }

        $_SESSION['success'] = "L'utilisateur a bien été mis à jour.";
        header('Location: index.php?route=administrator/users');
        exit;
    }

    /**
     * Affiche la page "Mon profil" de l'utilisateur connecté, quel que soit
     * son rôle. Une seule vue (users/profile.php) est utilisée pour tous les
     * rôles : elle adapte elle-même son menu latéral selon le rôle en session.
     * (GET index.php?route=student/profile | teacher/profile
     *                     | logistics/profile | administrator/profile)
     */
    public function profile()
    {
        checkAuth();

        $userId = (int) $_SESSION['user']['id'];
        $user   = User::findById($userId);

        if (!$user) {
            // Sécurité : l'utilisateur en session n'existe plus en base
            header('Location: index.php?route=logout');
            exit;
        }

        $userName = htmlspecialchars($_SESSION['user']['name']);

        require __DIR__ . '/../views/users/profile.php';
    }

    /**
     * Traite la modification du profil (nom, email, mot de passe) de
     * l'utilisateur connecté, quel que soit son rôle.
     * (POST index.php?route=student/profile/update | teacher/profile/update
     *                      | logistics/profile/update | administrator/profile/update)
     */
    public function updateProfile()
    {
        checkAuth();

        $userId       = (int) $_SESSION['user']['id'];
        $role         = $_SESSION['user']['role'];
        $profileRoute = self::PROFILE_ROUTES[$role] ?? 'home';

        $name            = trim($_POST['nom'] ?? '');
        $email           = trim($_POST['email'] ?? '');
        $newPassword     = trim($_POST['new_password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');

        if ($name === '' || $email === '') {
            $_SESSION['error'] = "Veuillez remplir le nom complet et l'adresse email.";
            header('Location: index.php?route=' . $profileRoute);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "L'adresse email saisie n'est pas valide.";
            header('Location: index.php?route=' . $profileRoute);
            exit;
        }

        if (User::findByEmailExcludingId($email, $userId)) {
            $_SESSION['error'] = "Cette adresse email est déjà utilisée par un autre compte.";
            header('Location: index.php?route=' . $profileRoute);
            exit;
        }

        // La modification du mot de passe est facultative : on ne la traite
        // que si l'un des deux champs a été rempli.
        if ($newPassword !== '' || $confirmPassword !== '') {
            if (strlen($newPassword) < 8) {
                $_SESSION['error'] = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
                header('Location: index.php?route=' . $profileRoute);
                exit;
            }

            if ($newPassword !== $confirmPassword) {
                $_SESSION['error'] = "Les deux mots de passe ne correspondent pas.";
                header('Location: index.php?route=' . $profileRoute);
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

        header('Location: index.php?route=' . $profileRoute);
        exit;
    }
}
