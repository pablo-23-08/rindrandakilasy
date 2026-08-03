<?php
// ═══════════════════════════════════════════════
// MODEL Session
// Gère uniquement la persistance des sessions en base de données
// (table `sessions`). Séparé de User.php pour respecter le
// principe de responsabilité unique (SRP) et l'architecture MVC :
// User.php ne s'occupe que des utilisateurs, Session.php des sessions.
// ═══════════════════════════════════════════════

require_once __DIR__ . '/../core/Database.php';

class Session
{
    /**
     * Durée de validité d'une session, en secondes (2 heures).
     * Utilisée pour calculer la colonne `expires_at`.
     */
    private const LIFETIME = 7200;

    /**
     * Enregistre une session en base de données.
     * Appelé juste après une connexion réussie.
     * REPLACE INTO = INSERT si la session n'existe pas encore, UPDATE si elle existe déjà.
     */
    public static function create($sessionId, $userId)
    {
        $db = Database::connect();

        $expiresAt = date('Y-m-d H:i:s', time() + self::LIFETIME);

        $stmt = $db->prepare(
            "REPLACE INTO sessions (id_session, id_user, expires_at) VALUES (?, ?, ?)"
        );
        $stmt->execute([$sessionId, $userId, $expiresAt]);
    }

    /**
     * Recherche une session par son identifiant.
     * Utilisé pour vérifier qu'une session PHP est toujours valide côté base de données.
     * Retourne false si la session est absente ou expirée (et la supprime le cas échéant).
     */
    public static function find($sessionId)
    {
        $db = Database::connect();

        $stmt = $db->prepare("SELECT * FROM sessions WHERE id_session = ?");
        $stmt->execute([$sessionId]);

        $session = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$session) {
            return false;
        }

        if (strtotime($session['expires_at']) < time()) {
            self::delete($sessionId);
            return false;
        }

        return $session;
    }

    /**
     * Met à jour la date d'expiration d'une session (prolonge sa validité).
     */
    public static function touch($sessionId)
    {
        $db = Database::connect();

        $expiresAt = date('Y-m-d H:i:s', time() + self::LIFETIME);

        $stmt = $db->prepare("UPDATE sessions SET expires_at = ? WHERE id_session = ?");
        $stmt->execute([$expiresAt, $sessionId]);
    }

    /**
     * Supprime une session de la base de données.
     * Appelé lors de la déconnexion.
     */
    public static function delete($sessionId)
    {
        $db = Database::connect();

        $stmt = $db->prepare("DELETE FROM sessions WHERE id_session = ?");
        $stmt->execute([$sessionId]);
    }
}
