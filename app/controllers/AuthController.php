<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/User.php';

class AuthController extends Controller {

    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    // GET : affiche le formulaire de connexion
    public function loginForm(): void {
        // Redirige si déjà connecté
        if (!empty($_SESSION['user'])) {
            $this->redirectByRole($_SESSION['user']['role']);
        }

        $this->render('auth/login', ['error' => null]);
    }

    // POST : traite la connexion
    public function login(): void {
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        // Validation basique
        if (empty($email) || empty($password)) {
            $this->render('auth/login', ['error' => 'Veuillez remplir tous les champs.']);
            return;
        }

        // Cherche l'utilisateur
        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->render('auth/login', ['error' => 'Email ou mot de passe incorrect.']);
            return;
        }

        // Récupère le rôle
        $roleInfo = $this->userModel->getRoleData($user['idUser']);

        // Stocke en session
        $_SESSION['user'] = [
            'idUser' => $user['idUser'],
            'name'   => $user['name'],
            'email'  => $user['email'],
            'role'   => $roleInfo['role'],
            'data'   => $roleInfo['data'],
        ];

        // Redirige selon le rôle
        $this->redirectByRole($roleInfo['role']);
    }

    // GET : déconnexion
    public function logout(): void {
        session_destroy();
        $this->redirect('index.php?url=auth/login');
    }

    // Redirige selon le rôle
    private function redirectByRole(string $role): void {
        $destinations = [
            'directeur'  => 'index.php?url=admin/dashboard',
            'professeur' => 'index.php?url=memoire',
            'etudiant'   => 'index.php?url=memoire',
            'consulteur' => 'index.php?url=memoire',
        ];

        $this->redirect($destinations[$role] ?? 'index.php?url=memoire');
    }
}