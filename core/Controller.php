<?php

abstract class Controller {

    // -------------------------------------------------------
    // Charger une vue avec des données
    // Exemple : $this->render('auth/login', ['error' => '...'])
    // -------------------------------------------------------
    protected function render(string $view, array $data = []): void {
        // Rendre les variables du tableau accessibles dans la vue
        extract($data);

        $viewPath = __DIR__ . '/../app/views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            die("Vue introuvable : {$view}");
        }

        require_once $viewPath;
    }

    // -------------------------------------------------------
    // Rediriger vers une URL
    // -------------------------------------------------------
    protected function redirect(string $url): void {
        header("Location: {$url}");
        exit();
    }

    // -------------------------------------------------------
    // Retourner une réponse JSON (pour les requêtes AJAX)
    // -------------------------------------------------------
    protected function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    // -------------------------------------------------------
    // Vérifier si la requête est de type POST
    // -------------------------------------------------------
    protected function isPost(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    // -------------------------------------------------------
    // Vérifier si la requête est AJAX
    // -------------------------------------------------------
    protected function isAjax(): bool {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    // -------------------------------------------------------
    // Récupérer et nettoyer une donnée POST
    // -------------------------------------------------------
    protected function post(string $key, string $default = ''): string {
        return isset($_POST[$key])
            ? htmlspecialchars(trim($_POST[$key]), ENT_QUOTES, 'UTF-8')
            : $default;
    }

    // -------------------------------------------------------
    // Récupérer et nettoyer une donnée GET
    // -------------------------------------------------------
    protected function get(string $key, mixed $default = null): mixed {
        return isset($_GET[$key])
            ? htmlspecialchars(trim($_GET[$key]), ENT_QUOTES, 'UTF-8')
            : $default;
    }

    // -------------------------------------------------------
    // Vérifier si l'utilisateur est connecté
    // -------------------------------------------------------
    protected function estConnecte(): bool {
        return isset($_SESSION['idUser']);
    }

    // -------------------------------------------------------
    // Vérifier le rôle de l'utilisateur connecté
    // -------------------------------------------------------
    protected function estRole(string $role): bool {
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }

    // -------------------------------------------------------
    // Protéger une page — redirige si non connecté
    // -------------------------------------------------------
    protected function requiertConnexion(): void {
        if (!$this->estConnecte()) {
            $this->redirect('/public/index.php?route=login');
        }
    }

    // -------------------------------------------------------
    // Protéger une page — redirige si mauvais rôle
    // -------------------------------------------------------
    protected function requiertRole(string $role): void {
        $this->requiertConnexion();
        if (!$this->estRole($role)) {
            $this->redirect('/public/index.php?route=accueil');
        }
    }

    // -------------------------------------------------------
    // Protéger une page — accepte plusieurs rôles
    // -------------------------------------------------------
    protected function requiertRoles(array $roles): void {
        $this->requiertConnexion();
        if (!in_array($_SESSION['role'] ?? '', $roles)) {
            $this->redirect('/public/index.php?route=accueil');
        }
    }
}