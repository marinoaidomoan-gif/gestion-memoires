<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Memoire.php';

class AdminController extends Controller {

    private User    $userModel;
    private Memoire $memoireModel;

    public function __construct() {
        $this->userModel    = new User();
        $this->memoireModel = new Memoire();
    }

    // GET : tableau de bord
    public function dashboard(): void {
        $this->requireRole('directeur');

        $stats = [
            'total_memoires'   => $this->memoireModel->count(),
            'en_attente'       => $this->memoireModel->count('statut = ?', ['en_attente']),
            'valides'          => $this->memoireModel->count('statut = ?', ['valide']),
            'rejetes'          => $this->memoireModel->count('statut = ?', ['rejete']),
            'total_users'      => $this->userModel->count(),
        ];

        $memoires_recents = $this->memoireModel->getEnAttente();

        $this->render('admin/dashboard', [
            'user'             => $_SESSION['user'],
            'stats'            => $stats,
            'memoires_recents' => $memoires_recents,
        ]);
    }

    // GET : liste de tous les utilisateurs
    public function users(): void {
        $this->requireRole('directeur');
        $users = $this->userModel->all();
        $this->render('admin/users', [
            'user'   => $_SESSION['user'],
            'users'  => $users,
            'erreurs' => [],
        ]);
    }

    // POST : créer un utilisateur
    public function creerUser(): void {
        $this->requireRole('directeur');

        $erreurs = [];
        $role    = $this->input('role');
        $name    = $this->input('name');
        $email   = $this->input('email');
        $password = $this->input('password');

        if (empty($name))     $erreurs[] = 'Le nom est obligatoire.';
        if (empty($email))    $erreurs[] = "L'email est obligatoire.";
        if (empty($password)) $erreurs[] = 'Le mot de passe est obligatoire.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = 'Email invalide.';

        // Vérifie si email déjà pris
        if (empty($erreurs) && $this->userModel->findByEmail($email)) {
            $erreurs[] = 'Cet email est déjà utilisé.';
        }

        if (!empty($erreurs)) {
            $users = $this->userModel->all();
            $this->render('admin/users', [
                'user'   => $_SESSION['user'],
                'users'  => $users,
                'erreurs' => $erreurs,
            ]);
            return;
        }

        // Données communes
        $userData = [
            'name'             => $name,
            'email'            => $email,
            'password'         => $password,
            'date_inscription' => date('Y-m-d'),
        ];

        // Données spécifiques au rôle
        $roleData = match($role) {
            'etudiant'   => [
                'niveau'        => $this->input('niveau'),
                'filiere'       => $this->input('filiere'),
                'annee_diplome' => $this->input('annee_diplome') ?: null,
            ],
            'consulteur' => [
                'niveau'  => $this->input('niveau'),
                'filiere' => $this->input('filiere'),
            ],
            'professeur' => [
                'specialite'  => $this->input('specialite'),
                'grade'       => $this->input('grade'),
                'departement' => $this->input('departement'),
            ],
            'directeur'  => [
                'bureau' => $this->input('bureau'),
            ],
            default => []
        };

        $this->userModel->creerAvecRole($userData, $role, $roleData);
        $this->redirect('index.php?url=admin/users');
    }

    // POST : supprimer un utilisateur
    public function supprimerUser(string $id): void {
        $this->requireRole('directeur');

        // Ne peut pas se supprimer lui-même
        if ((int)$id === $_SESSION['user']['idUser']) {
            $this->redirect('index.php?url=admin/users');
            return;
        }

        $this->userModel->delete((int)$id);
        $this->redirect('index.php?url=admin/users');
    }
}