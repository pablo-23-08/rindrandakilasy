<?php
// ═══════════════════════════════════════════════
// Gestion de session + contrôle d'accès (auth & rôles)
// Chargé une seule fois par config/bootstrap.php
// ═══════════════════════════════════════════════

require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Session.php';

// Démarrage de session sécurisé (une seule fois même si le fichier est inclus plusieurs fois)
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,       // Le cookie expire à la fermeture du navigateur
        'path'     => '/',     // Valable pour tout le site
        'secure'   => false,   // Mettre à true en production si HTTPS est disponible
        'httponly' => true,    // Interdit l'accès au cookie depuis JavaScript
        'samesite' => 'Lax',   // Protection CSRF de base
    ]);
    session_start();
}

/**
 * Associe chaque rôle à la route de son tableau de bord.
 */
function dashboardRoute(string $role): string
{
    $routes = [
        'admin'                 => 'administrator/dashboard',
        'teacher'               => 'teacher/dashboard',
        'student'                => 'student/dashboard',
        'logistics_department'  => 'logistics/dashboard',
    ];

    return $routes[$role] ?? 'home';
}

/**
 * Vide complètement la session PHP (variables + cookie côté navigateur).
 * Utilisé lors de la déconnexion ou quand une session est invalide.
 */
function clearSession(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

/**
 * Vérifie que l'utilisateur est connecté ET que sa session existe toujours en base.
 * Redirige vers la page de connexion si ce n'est pas le cas.
 */
function checkAuth(): void
{
    if (!isset($_SESSION['user'])) {
        header('Location: index.php?route=home');
        exit();
    }

    $session = Session::find(session_id());

    // Session absente ou ne correspondant pas à l'utilisateur en session PHP → invalide
    if (!$session || (int) $session['id_user'] !== (int) $_SESSION['user']['id']) {
        clearSession();
        header('Location: index.php?route=home');
        exit();
    }

    // Met à jour l'horodatage d'activité
    Session::touch(session_id());
}

/**
 * Vérifie que l'utilisateur est connecté ET qu'il possède l'un des rôles autorisés.
 * Exemple : checkRole('student') empêche un enseignant/administrator/logistique d'accéder à la page.
 *
 * @param string|array $allowedRoles Un rôle ou un tableau de rôles autorisés
 */
function checkRole(string|array $allowedRoles): void
{
    checkAuth(); // On vérifie d'abord que l'utilisateur est bien connecté

    $allowedRoles = is_array($allowedRoles) ? $allowedRoles : [$allowedRoles];

    if (!in_array($_SESSION['user']['role'], $allowedRoles, true)) {
        // Rôle non autorisé → renvoyé vers SON propre tableau de bord, pas d'erreur affichée
        header('Location: index.php?route=' . dashboardRoute($_SESSION['user']['role']));
        exit();
    }
}

/**
 * Redirige un utilisateur déjà connecté vers son tableau de bord.
 * Empêche d'accéder à la page de connexion une fois authentifié.
 */
function redirectIfLogged(): void
{
    if (!isset($_SESSION['user'])) {
        return; // Pas connecté → on laisse afficher la page de connexion
    }

    $session = Session::find(session_id());

    if (!$session || (int) $session['id_user'] !== (int) $_SESSION['user']['id']) {
        // Session invalide côté serveur : on nettoie proprement et on laisse passer
        clearSession();
        return;
    }

    Session::touch(session_id());

    header('Location: index.php?route=' . dashboardRoute($_SESSION['user']['role']));
    exit();
}
