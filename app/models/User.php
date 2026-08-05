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
     * Rôles que l'administrateur est autorisé à créer/modifier depuis la
     * page "Gestion des utilisateurs" (admin/users). Les comptes "admin" et
     * "logistics_department" ne sont pas gérés depuis cette page.
     */
    private const MANAGEABLE_ROLES = ['student', 'teacher'];

    /**
     * Libellés lisibles (français) des rôles stockés en base.
     */
    private const ROLE_LABELS = [
        'admin'                 => 'Administrateur',
        'teacher'                => 'Enseignant',
        'student'                => 'Etudiant',
        'logistics_department'  => 'Service logistique',
    ];

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

    /**
     * Liste des rôles que l'administrateur peut créer/modifier depuis la
     * page "Gestion des utilisateurs" (Etudiant, Enseignant).
     */
    public static function manageableRoles(): array
    {
        return self::MANAGEABLE_ROLES;
    }

    /**
     * Indique si un rôle donné fait partie des rôles gérables par
     * l'administrateur depuis la page "Gestion des utilisateurs".
     */
    public static function isManageableRole(string $role): bool
    {
        return in_array($role, self::MANAGEABLE_ROLES, true);
    }

    /**
     * Convertit un rôle technique (base de données) en libellé lisible.
     */
    public static function roleLabel(string $role): string
    {
        return self::ROLE_LABELS[$role] ?? $role;
    }

    /**
     * Récupère les utilisateurs "étudiant"/"enseignant" avec, pour chacun,
     * le nombre de réservations faites / validées / refusées.
     * Un filtre optionnel sur le rôle et une recherche optionnelle sur le
     * nom peuvent être appliqués.
     * Utilisé par la page "Gestion des utilisateurs" de l'administrateur.
     */
    public static function findManageableWithReservationStats(?string $role = null, ?string $search = null): array
    {
        $db = Database::connect();

        $sql = "SELECT
                    u.id,
                    u.name,
                    u.email,
                    u.role,
                    COUNT(r.id) AS reservations_made,
                    SUM(CASE WHEN r.status = 'approved' THEN 1 ELSE 0 END) AS reservations_approved,
                    SUM(CASE WHEN r.status = 'refused' THEN 1 ELSE 0 END) AS reservations_refused
                FROM users u
                LEFT JOIN reservations r ON r.id_user = u.id
                WHERE u.role IN ('student', 'teacher')";

        $params = [];

        if (!empty($role) && self::isManageableRole($role)) {
            $sql .= " AND u.role = ?";
            $params[] = $role;
        }

        if (!empty($search)) {
            $sql .= " AND u.name LIKE ?";
            $params[] = '%' . $search . '%';
        }

        $sql .= " GROUP BY u.id, u.name, u.email, u.role ORDER BY u.name ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crée un nouvel utilisateur (mot de passe déjà haché) et retourne son id.
     * Utilisé par l'administrateur pour ajouter un étudiant ou un enseignant.
     */
    public static function create(string $name, string $email, string $hashedPassword, string $role): int
    {
        $db = Database::connect();

        $stmt = $db->prepare(
            "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$name, $email, $hashedPassword, $role]);

        return (int) $db->lastInsertId();
    }

    /**
     * Met à jour le nom, l'email et le rôle d'un utilisateur.
     * Utilisé par l'administrateur depuis la page "Gestion des utilisateurs"
     * (contrairement à updateProfile(), qui ne modifie jamais le rôle).
     */
    public static function updateByAdmin(int $id, string $name, string $email, string $role): bool
    {
        $db = Database::connect();

        $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");

        return $stmt->execute([$name, $email, $role, $id]);
    }
}
