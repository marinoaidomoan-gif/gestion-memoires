<?php

class Router {
    private array $routes = [];

    // Enregistre une route
    // Exemple : $router->add('GET', 'memoire/afficher', 'MemoireController', 'afficher');
    public function add(string $method, string $path, string $controller, string $action): void {
        $this->routes[] = [
            'method'     => strtoupper($method),
            'path'       => trim($path, '/'),
            'controller' => $controller,
            'action'     => $action,
        ];
    }

    // Lance le routage
    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'];

         //Sans .htaccess : index.php?url=memoire/afficher/3
        // Avec .htaccess : /memoire/afficher/3  (décommenter le bloc ci-dessous)
        $uri = trim($_GET['url'] ?? '', '/');

        // --- Décommenter si tu actives .htaccess plus tard ---
        // $uri  = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        // $base = trim(dirname($_SERVER['SCRIPT_NAME']), '/');
        // if ($base && str_starts_with($uri, $base)) {
        //     $uri = trim(substr($uri, strlen($base)), '/');
        // }
        // ------------------------------------------------------

        foreach ($this->routes as $route) {
            // Transforme :id en groupe de capture regex
            $pattern = preg_replace('/:[a-zA-Z0-9_]+/', '([^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // retire la correspondance complète
                $controllerFile = __DIR__ . '/../app/controllers/' . $route['controller'] . '.php';

                if (!file_exists($controllerFile)) {
                    $this->abort(500, "Controller introuvable : {$route['controller']}");
                }

                require_once $controllerFile;
                $ctrl = new $route['controller']();

                if (!method_exists($ctrl, $route['action'])) {
                    $this->abort(500, "Action introuvable : {$route['action']}");
                }

                call_user_func_array([$ctrl, $route['action']], $matches);
                return;
            }
        }

        $this->abort(404, 'Page introuvable');
    }

    private function abort(int $code, string $message): void {
        http_response_code($code);
        echo "<h1>Erreur $code</h1><p>$message</p>";
        exit;
    }
}