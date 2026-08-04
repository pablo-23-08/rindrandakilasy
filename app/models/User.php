<?php
// ═══════════════════════════════════════════════
// MODEL User
// Gère uniquement la persistance des utilisateurs (table `users`).
// La gestion des sessions (table `sessions`) est déléguée au model
// Session.php afin de respecter le principe de responsabilité unique (SRP).
// ═══════════════════════════════════════════════

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

    /**
     * Recherche un utilisateur par email en excluant un id donné.
     * Utilisé pour vérifier qu'un email n'est pas déjà utilisé par un AUTRE
     * compte lors de la modification du profil.
     */
    public static function findByEmailExcludingId($email, $id)
    {
        $db = Database::connect();

        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Met à jour le nom et l'email d'un utilisateur (page "Mon profil").
     */
    public static function updateProfile($id, $name, $email)
    {
        $db = Database::connect();

        $stmt = $db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");

        return $stmt->execute([$name, $email, $id]);
    }

    /**
     * Met à jour le mot de passe (déjà haché) d'un utilisateur.
     */
    public static function updatePassword($id, $hashedPassword)
    {
        $db = Database::connect();

        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");

        return $stmt->execute([$hashedPassword, $id]);
    }
}
