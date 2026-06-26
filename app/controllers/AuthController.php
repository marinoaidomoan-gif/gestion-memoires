<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/EtudiantDiplome.php';
require_once __DIR__ . '/../models/EtudiantConsulteur.php';

class AuthController extends Controller {

    private User $user;

    public function __construct() {
        $this->user = new User();
    }

    // -------------------------------------------------------
    // GET  /index.php?route=login
    // POST /index.php?route=login  → traitement connexion
    // -------------------------------------------------------
    public function login(): void {
        if ($this->estConnecte()) {
            $this->redirectParRole();
            return;
        }

        $error = null;

        if ($this->isPost()) {
            $email    = $this->post('email');
            $password = $this->post('password');

            if (empty($email) || empty($password)) {
                $error = "Veuillez remplir tous les champs.";
            } else {
                if ($this->user->seConnecter($email, $password)) {
                    $this->redirectParRole();
                    return;
                }
                $error = "Email ou mot de passe incorrect.";
            }
        }

        $this->render('auth/login', ['error' => $error]);
    }

    // -------------------------------------------------------
    // GET  /index.php?route=register
    // POST /index.php?route=register → traitement inscription
    // Accessible uniquement pour etudiant_diplome et etudiant_consulteur
    // -------------------------------------------------------
    public function register(): void {
        if ($this->estConnecte()) {
            $this->redirectParRole();
            return;
        }

        $error   = null;
        $success = null;

        if ($this->isPost()) {
            $role = $this->post('role');

            // Seuls ces deux rôles peuvent s'inscrire librement
            if (!in_array($role, ['etudiant_diplome', 'etudiant_consulteur'])) {
                $error = "Rôle non autorisé pour l'inscription.";
            } else {
                $data = [
                    'name'     => $this->post('name'),
                    'email'    => $this->post('email'),
                    'password' => $this->post('password'),
                    'role'     => $role,
                    'niveau'   => $this->post('niveau'),
                    'filiere'  => $this->post('filiere'),
                ];

                // Validation basique
                $error = $this->validerInscription($data);

                if (!$error) {
                    // Vérifier que l'email n'existe pas déjà
                    if ($this->user->findByEmail($data['email'])) {
                        $error = "Cet email est déjà utilisé.";
                    } else {
                        try {
                            if ($role === 'etudiant_diplome') {
                                $data['annee_diplome'] = $this->post('annee_diplome') ?: null;
                                (new EtudiantDiplome())->creerCompteEtudiant($data);
                            } else {
                                (new EtudiantConsulteur())->creerCompteConsulteur($data);
                            }
                            $success = "Compte créé avec succès. Vous pouvez vous connecter.";
                        } catch (Exception $e) {
                            $error = "Erreur système : " . $e->getMessage();
                        }
                    }
                }
            }
        }

        $this->render('auth/register', [
            'error'   => $error,
            'success' => $success,
        ]);
    }

    // -------------------------------------------------------
    // GET /index.php?route=logout
    // -------------------------------------------------------
    public function logout(): void {
        $this->user->seDeconnecter();
        $this->redirect('/gestion_memoires/public/index.php?route=login');
    }

    // -------------------------------------------------------
    // Rediriger selon le rôle après connexion
    // -------------------------------------------------------
    private function redirectParRole(): void {
        $role = $_SESSION['role'] ?? '';

        $routes = [
            'etudiant_diplome'    => 'etudiant/dashboard',
            'etudiant_consulteur' => 'etudiant/dashboard',
            'professeur'          => 'professeur/dashboard',
            'directeur_etudes'    => 'admin/dashboard',
        ];

        $route = $routes[$role] ?? 'accueil';
        $this->redirect('/gestion_memoires/public/index.php?route=' . $route);
    }

    // -------------------------------------------------------
    // Validation des données d'inscription
    // Retourne un message d'erreur ou null si OK
    // -------------------------------------------------------
    private function validerInscription(array $data): ?string {
        if (empty($data['name']))    return "Le nom est obligatoire.";
        if (empty($data['email']))   return "L'email est obligatoire.";
        if (empty($data['password'])) return "Le mot de passe est obligatoire.";
        if (empty($data['niveau']))  return "Le niveau est obligatoire.";
        if (empty($data['filiere'])) return "La filière est obligatoire.";

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return "L'adresse email n'est pas valide.";
        }

        if (strlen($data['password']) < 6) {
            return "Le mot de passe doit contenir au moins 6 caractères.";
        }

        return null;
    }
}