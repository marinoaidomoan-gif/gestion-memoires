<?php

class Router {

    // Table des routes : 'route' => ['Controller', 'methode']
    private $routes = [];

    // -------------------------------------------------------
    // Enregistrer une route
    // -------------------------------------------------------
    public function add(string $route, string $controller, string $method): void {
        $this->routes[$route] = [
            'controller' => $controller,
            'method'     => $method,
        ];
    }

    // -------------------------------------------------------
    // Lancer le routage
    // -------------------------------------------------------
    public function dispatch(): void {
        // Récupérer la route depuis l'URL (?route=login)
        $route = $_GET['route'] ?? 'accueil';

        if (!isset($this->routes[$route])) {
            $this->notFound();
            return;
        }

        $controllerName = $this->routes[$route]['controller'];
        $methodName     = $this->routes[$route]['method'];

        // Charger le controller
        $controllerPath = __DIR__ . '/../app/controllers/' . $controllerName . '.php';

        if (!file_exists($controllerPath)) {
            $this->notFound();
            return;
        }

        require_once $controllerPath;

        if (!class_exists($controllerName)) {
            $this->notFound();
            return;
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $methodName)) {
            $this->notFound();
            return;
        }

        // Appeler la méthode du controller
        $controller->$methodName();
    }

    // -------------------------------------------------------
    // Page 404
    // -------------------------------------------------------
    private function notFound(): void {
        http_response_code(404);
        echo "<h1>404 — Page introuvable</h1>";
        exit();
    }
}