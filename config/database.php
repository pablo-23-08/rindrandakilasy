<?php

// Paramètres de connexion à la base de données MySQL.
// Ce fichier retourne un simple tableau, utilisé par app/core/Database.php

return [
    'host'     => 'localhost',
    'dbname'   => 'rindrandakilasy_db',
    'username' => 'root',
    'password' => '',
    'charset'  => 'utf8mb4'
];

// ─────────────────────────────────────────────
// Exemple de configuration pour un hébergement AlwaysData
// (dossier racine du site : www/rindrandakilasy/public/)
// Il suffit de remplacer les valeurs ci-dessus par celles-ci :
// ─────────────────────────────────────────────
//
// return [
//     'host'     => 'mysql-rindrandakilasy.alwaysdata.net',
//     'dbname'   => 'rindrandakilasy_db',
//     'username' => 'rindrandakilasy',
//     'password' => 'Rindrandakilasy.2026',
//     'charset'  => 'utf8mb4'
// ];
