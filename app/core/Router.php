<?php

class Router
{
    private $routes;

    public function __construct()
    {
        $this->routes = require __DIR__ . '/../../config/routes.php';
    }

    public function dispatch()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $projectFolder = '/rindrandakilasy/public/';

        if (strpos($uri, $projectFolder) === 0) {
            $uri = substr($uri, strlen($projectFolder));
        } 

        if ($uri == '') {
            $uri = '/';
        }

        if (isset($this->routes[$uri])) {
            
            $route = $this->routes[$uri];

            // 1. Si c'est un contrôleur (ex: POST login)
            if (isset($route['controller'])) {
                require_once __DIR__ . '/../controllers/' . $route['controller'] . '.php';
                $controller = new $route['controller']();
                $action = $route['action'];
                $controller->$action();
            } 
            // 2. Sinon, c'est une vue (ex: GET / pour afficher le formulaire)
            else if (isset($route['view'])) {
                require __DIR__ . '/../views/' . $route['view'];
            }

        } else {
            echo "<h1>404 - Page introuvable</h1>";
        }
    }
}