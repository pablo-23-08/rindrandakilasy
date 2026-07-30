<?php

require_once __DIR__ . '/../models/User.php';

class AuthController
{
    public function login()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email    = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = "Veuillez remplir tous les champs.";
            header("Location: /rindrandakilasy/public/");
            exit;
        }

        $user = User::findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {

            $_SESSION['user'] = [
                'id'   => $user['id'],
                'name' => $user['name'],
                'role' => $user['role']
            ];

            // Redirection selon le rôle
            $role = $user['role'];

            if ($role === 'admin') {
                header("Location: /rindrandakilasy/public/administrator/dashboard");
            } elseif ($role === 'teacher') {
                header("Location: /rindrandakilasy/public/teacher/dashboard");
            } elseif ($role === 'logistics_department') {
                header("Location: /rindrandakilasy/public/logistics/dashboard");
            } else {
                header("Location: /rindrandakilasy/public/student/dashboard");
            }
            exit;

        } else {
            $_SESSION['error'] = "Email ou mot de passe incorrect";
            header("Location: /rindrandakilasy/public/");
            exit;
        }
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_destroy();
        header("Location: /rindrandakilasy/public/");
        exit;
    }
}