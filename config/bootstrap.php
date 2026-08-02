<?php
// ═══════════════════════════════════════════════
// Point d'entrée de configuration.
// Chargé en premier par public/index.php sur chaque requête.
// ═══════════════════════════════════════════════

require_once __DIR__ . '/../app/core/Database.php'; // Connexion PDO (singleton Database::connect())
require_once __DIR__ . '/auth.php';                 // Session + fonctions checkAuth(), checkRole(), etc.
