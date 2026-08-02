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

    /**
     * Enregistre une session en base de données.
     * Appelé juste après une connexion réussie.
     * REPLACE INTO = INSERT si la session n'existe pas encore, UPDATE si elle existe déjà.
     */
    public static function createSession($sessionId, $userId, $role)
    {
        $db = Database::connect();

        $stmt = $db->prepare(
            "REPLACE INTO session (id_session, id_user, role, initial) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$sessionId, $userId, $role, time()]);
    }

    /**
     * Recherche une session par son identifiant.
     * Utilisé pour vérifier qu'une session PHP est toujours valide côté base de données.
     */
    public static function findSession($sessionId)
    {
        $db = Database::connect();

        $stmt = $db->prepare("SELECT * FROM session WHERE id_session = ?");
        $stmt->execute([$sessionId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Met à jour l'horodatage d'activité d'une session (prolonge sa validité).
     */
    public static function touchSession($sessionId)
    {
        $db = Database::connect();

        $stmt = $db->prepare("UPDATE session SET initial = ? WHERE id_session = ?");
        $stmt->execute([time(), $sessionId]);
    }

    /**
     * Supprime une session de la base de données.
     * Appelé lors de la déconnexion.
     */
    public static function deleteSession($sessionId)
    {
        $db = Database::connect();

        $stmt = $db->prepare("DELETE FROM session WHERE id_session = ?");
        $stmt->execute([$sessionId]);
    }
}
