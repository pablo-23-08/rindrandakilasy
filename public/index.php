<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once '../app/core/Database.php';
    require_once '../app/core/Router.php';

    /*
    |--------------------------------------------------------------------------
    | Test de connexion PDO
    |--------------------------------------------------------------------------
    */

    // try {

    //     Database::connect();

    //     // echo "Connexion réussie";

    // } catch (Exception $e) {

    //     die($e->getMessage());

    // }

    /*
    |--------------------------------------------------------------------------
    | Lancement du routeur
    |--------------------------------------------------------------------------
    */

    $router = new Router();
    $router->dispatch();