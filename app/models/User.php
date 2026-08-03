<?php

require_once __DIR__ . '/../core/Database.php';

class User
{
    /**
     * Recherche un utilisateur par son email. Utilisé lors de la connexion.
     */
    public static function findByEmail($email)
    {
        $db = Database::connect();

        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Recherche un utilisateur par son id.
     */
    public static function findById($id)
    {
        $db = Database::connect();

        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
