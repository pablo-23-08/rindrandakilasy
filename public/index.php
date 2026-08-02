<?php
// ═══════════════════════════════════════════════
// FRONT CONTROLLER — Point d'entrée unique du site
// ═══════════════════════════════════════════════
//
// Toutes les URLs passent par ce fichier.
// Exemple : index.php?route=student/dashboard
//
// Grâce au routage par paramètre GET (au lieu d'une réécriture d'URL),
// l'application fonctionne à l'identique quel que soit le dossier
// dans lequel "public/" est hébergé (local, sous-dossier, racine AlwaysData...).

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../app/core/Router.php';

$router = new Router();
$router->dispatch();
