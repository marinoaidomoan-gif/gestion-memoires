<?php

abstract class Controller {

    // Charge une vue avec des données
    // Exemple : $this->render('memoire/liste', ['memoires' => $data]);
    protected function render(string $view, array $data = []): void {
        // Rend les clés du tableau accessibles comme variables dans la vue
        extract($data);

        $viewFile = __DIR__ . '/../app/views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            http_response_code(500);
            die("Vue introuvable : $view");
        }

        require $viewFile;
    }

    // Redirige vers une URL
    protected function redirect(string $url): void {
        header("Location: $url");
        exit;
    }

    // Retourne une réponse JSON (pour les appels AJAX)
    protected function json(mixed $data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // Vérifie si l'utilisateur est connecté
    protected function requireAuth(): void {
        if (empty($_SESSION['user'])) {
            $this->redirect('/auth/login');
        }
    }

    // Vérifie le rôle de l'utilisateur connecté
    // Exemple : $this->requireRole('directeur');
    protected function requireRole(string ...$roles): void {
        $this->requireAuth();
        if (!in_array($_SESSION['user']['role'] ?? '', $roles)) {
            http_response_code(403);
            die("<h1>Accès refusé</h1><p>Vous n'avez pas les droits nécessaires.</p>");
        }
    }

    // Récupère et nettoie une valeur POST
    protected function input(string $key, string $default = ''): string {
        return htmlspecialchars(trim($_POST[$key] ?? $default));
    }
}