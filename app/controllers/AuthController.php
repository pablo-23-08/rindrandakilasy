<?php
// ═══════════════════════════════════════════════
// CONTROLLER AuthController
// Gère : page de connexion, traitement du login, déconnexion
// ═══════════════════════════════════════════════

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Session.php';

class AuthController
{
    /**
     * Affiche la page de connexion (= page d'accueil de l'application).
     * Si l'utilisateur est déjà connecté, il est redirigé vers son tableau de bord.
     */
    public function home()
    {
        redirectIfLogged(); // Défini dans config/auth.php

        require __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Traite le formulaire de connexion (POST index.php?route=login).
     */
    public function login()
    {
        redirectIfLogged();

        $email    = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = "Veuillez remplir tous les champs.";
            header("Location: index.php?route=home");
            exit;
        }

        $user = User::findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['error'] = "Email ou mot de passe incorrect";
            header("Location: index.php?route=home");
            exit;
        }

        // Connexion réussie : régénère l'id de session (anti session fixation)
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'   => $user['id'],
            'name' => $user['name'],
            'role' => $user['role']
        ];

        // Enregistre également la session en base de données (table `sessions`)
        Session::create(session_id(), $user['id']);

        // Redirection selon le rôle de l'utilisateur
        header("Location: index.php?route=" . dashboardRoute($user['role']));
        exit;
    }

    /**
     * Déconnecte l'utilisateur : supprime la ligne de session en base
     * puis détruit la session PHP.
     */
    public function logout()
    {
        if (isset($_SESSION['user'])) {
            // Supprime la session de la base de données
            Session::delete(session_id());
        }

        // Vide et détruit la session PHP (variables + cookie)
        clearSession();

        header("Location: index.php?route=home");
        exit;
    }
}
