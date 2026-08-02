<?php

    /**
     * Routeur de l'application.
     * Lit la route demandée via ?route=... et déclenche le bon Controller/action.
     * Exemple : index.php?route=student/dashboard
     */
    class Router
    {
        private array $routes;

        public function __construct()
        {
            $this->routes = require __DIR__ . '/../../config/routes.php';
        }

        public function dispatch(): void
        {
            // Route demandée dans l'URL, "home" par défaut (page de connexion)
            $route = $_GET['route'] ?? 'home';

            if (!isset($this->routes[$route])) {
                http_response_code(404);
                echo "<h1>404 - Page introuvable</h1>";
                echo "<a href='index.php'>Retour à l'accueil</a>";
                return;
            }

            $definition     = $this->routes[$route];
            $controllerName = $definition['controller'];
            $action         = $definition['action'];

            require_once __DIR__ . '/../controllers/' . $controllerName . '.php';

            $controller = new $controllerName();
            $controller->$action();
        }
    }
