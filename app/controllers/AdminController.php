<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/DirecteurEtudes.php';
require_once __DIR__ . '/../models/Memoire.php';
require_once __DIR__ . '/../models/Professeur.php';
require_once __DIR__ . '/../models/EtudiantDiplome.php';
require_once __DIR__ . '/../models/EtudiantConsulteur.php';
require_once __DIR__ . '/../models/User.php';

class AdminController extends Controller {

    private DirecteurEtudes $directeur;
    private Memoire         $memoireModel;

    public function __construct() {
        $this->directeur    = new DirecteurEtudes();
        $this->memoireModel = new Memoire();
    }

    // -------------------------------------------------------
    // GET /index.php?route=admin/dashboard
    // Vue globale : mémoires en attente + stats + users
    // -------------------------------------------------------
    public function dashboard(): void {
        $this->requiertRole('directeur_etudes');

        $memoiresEnAttente = $this->directeur->getMemoiresEnAttente();
        $statsStatuts      = $this->memoireModel->countParStatut();
        $tousLesMemoires   = $this->memoireModel->findAll();
        $tousLesUsers      = $this->directeur->gererUsers();
        $profil            = $this->directeur->getMonProfil();

        // Liste des professeurs pour le select d'assignation
        $professeurs = $this->memoireModel->getProfesseurs();

        $this->render('admin/dashboard', [
            'memoiresEnAttente' => $memoiresEnAttente,
            'statsStatuts'      => $statsStatuts,
            'tousLesMemoires'   => $tousLesMemoires,
            'tousLesUsers'      => $tousLesUsers,
            'professeurs'       => $professeurs,
            'profil'            => $profil,
        ]);
    }

    // -------------------------------------------------------
    // GET  /index.php?route=admin/creer-compte
    // POST /index.php?route=admin/creer-compte
    // Créer un compte pour n'importe quel rôle
    // -------------------------------------------------------
    public function creerCompte(): void {
        $this->requiertRole('directeur_etudes');

        $error   = null;
        $success = null;

        if ($this->isPost()) {
            $role = $this->post('role');
            $rolesValides = [
                'etudiant_diplome',
                'etudiant_consulteur',
                'professeur',
                'directeur_etudes',
            ];

            if (!in_array($role, $rolesValides)) {
                $error = "Rôle invalide.";
            } else {
                $data = [
                    'name'     => $this->post('name'),
                    'email'    => $this->post('email'),
                    'password' => $this->post('password'),
                    'role'     => $role,
                ];

                $error = $this->validerDonneesCompte($data, $role);

                if (!$error) {
                    // Champs spécifiques selon le rôle
                    $data = array_merge($data, $this->extraireChampsRole($role));

                    if ((new User())->findByEmail($data['email'])) {
                        $error = "Cet email est déjà utilisé.";
                    } else {
                        try {
                            $idUser = $this->directeur->creerCompteUtilisateur($data);
                            $success = $idUser
                                ? "Compte créé avec succès (ID #{$idUser})."
                                : "Erreur lors de la création du compte.";
                        } catch (Exception $e) {
                            $error = "Une erreur est survenue : " . $e->getMessage();
                        }
                    }
                }
            }
        }

        $this->render('admin/creer_compte', [
            'error'   => $error,
            'success' => $success,
        ]);
    }

    // -------------------------------------------------------
    // POST /index.php?route=admin/modifier-user
    // Modifier nom et email d'un utilisateur
    // -------------------------------------------------------
    public function modifierUser(): void {
        $this->requiertRole('directeur_etudes');

        $idUser = (int) $this->post('idUser');
        $data   = [
            'name'  => $this->post('name'),
            'email' => $this->post('email'),
        ];

        if (!$idUser || empty($data['name']) || empty($data['email'])) {
            $this->repondre(false, "Données invalides.");
            return;
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->repondre(false, "Email invalide.");
            return;
        }

        $user = new User();
        $ok   = $user->modifierUser($idUser, $data);

        if ($this->isAjax()) {
            $this->json([
                'success' => $ok,
                'message' => $ok
                    ? "Utilisateur modifié avec succès."
                    : "Impossible de modifier cet utilisateur.",
            ]);
        } else {
            $this->redirect('/public/index.php?route=admin/dashboard');
        }
    }

    // -------------------------------------------------------
    // POST /index.php?route=admin/supprimer-user
    // Supprimer un utilisateur (sauf soi-même)
    // -------------------------------------------------------
    public function supprimerUser(): void {
        $this->requiertRole('directeur_etudes');

        $idUser = (int) $this->post('idUser');

        if (!$idUser) {
            $this->repondre(false, "Utilisateur introuvable.");
            return;
        }

        // Sécurité : un directeur ne peut pas se supprimer lui-même
        if ($idUser === $_SESSION['idUser']) {
            $this->repondre(false, "Vous ne pouvez pas supprimer votre propre compte.");
            return;
        }

        $user = new User();
        $ok   = $user->supprimerUser($idUser);

        if ($this->isAjax()) {
            $this->json([
                'success' => $ok,
                'message' => $ok
                    ? "Utilisateur supprimé."
                    : "Impossible de supprimer cet utilisateur.",
            ]);
        } else {
            $this->redirect('/public/index.php?route=admin/dashboard');
        }
    }

    // -------------------------------------------------------
    // POST /index.php?route=admin/supprimer-commentaire
    // Modération : supprimer n'importe quel commentaire
    // -------------------------------------------------------
    public function supprimerCommentaire(): void {
        $this->requiertRole('directeur_etudes');

        $idCommentaire = (int) $this->post('idCommentaire');

        if (!$idCommentaire) {
            $this->repondre(false, "Commentaire introuvable.");
            return;
        }

        $ok = $this->directeur->supprimerCommentaire($idCommentaire);

        if ($this->isAjax()) {
            $this->json([
                'success' => $ok,
                'message' => $ok ? "Commentaire supprimé." : "Erreur lors de la suppression.",
            ]);
        } else {
            $this->redirect('/public/index.php?route=admin/dashboard');
        }
    }

    // -------------------------------------------------------
    // GET /index.php?route=admin/profil
    // -------------------------------------------------------
        // GET /index.php?route=admin/profil
    public function profil(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        require_once __DIR__ . '/../models/DirecteurEtudes.php';
        $directeurModel = new DirecteurEtudes();
        $profil = $directeurModel->getMonProfil();

        // On envoie le profil ET les données d'arrière-plan déjà stockées dans ton contrôleur
        $this->render('admin/profil', [
            'profil'            => $profil,
            'memoiresEnAttente' => $this->memoiresEnAttente ?? [],
            'statsStatuts'      => $this->statsStatuts ?? ['en_attente' => 0, 'valide' => 0, 'rejete' => 0],
            'tousLesMemoires'   => $this->tousLesMemoires ?? [],
            'tousLesUsers'      => $this->tousLesUsers ?? [],
            'professeurs'       => $this->professeurs ?? []
        ]);
    }

    // -------------------------------------------------------
    // Helpers privés
    // -------------------------------------------------------

    private function validerDonneesCompte(array $data, string $role): ?string {
        if (empty($data['name']))     return "Le nom est obligatoire.";
        if (empty($data['email']))    return "L'email est obligatoire.";
        if (empty($data['password'])) return "Le mot de passe est obligatoire.";
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))
            return "L'adresse email n'est pas valide.";
        if (strlen($data['password']) < 6)
            return "Le mot de passe doit contenir au moins 6 caractères.";

        // Champs spécifiques au rôle
        if (in_array($role, ['etudiant_diplome', 'etudiant_consulteur'])) {
            if (empty($_POST['niveau']))  return "Le niveau est obligatoire.";
            if (empty($_POST['filiere'])) return "La filière est obligatoire.";
        }
        if ($role === 'professeur') {
            if (empty($_POST['specialite']))  return "La spécialité est obligatoire.";
            if (empty($_POST['grade']))       return "Le grade est obligatoire.";
            if (empty($_POST['departement'])) return "Le département est obligatoire.";
        }
        if ($role === 'directeur_etudes') {
            if (empty($_POST['bureau'])) return "Le bureau est obligatoire.";
        }

        return null;
    }

    private function extraireChampsRole(string $role): array {
        return match($role) {
            'etudiant_diplome' => [
                'niveau'        => $this->post('niveau'),
                'filiere'       => $this->post('filiere'),
                'annee_diplome' => $this->post('annee_diplome') ?: null,
            ],
            'etudiant_consulteur' => [
                'niveau'  => $this->post('niveau'),
                'filiere' => $this->post('filiere'),
            ],
            'professeur' => [
                'specialite'  => $this->post('specialite'),
                'grade'       => $this->post('grade'),
                'departement' => $this->post('departement'),
            ],
            'directeur_etudes' => [
                'bureau' => $this->post('bureau'),
            ],
            default => [],
        };
    }

    private function repondre(bool $success, string $message): void {
        if ($this->isAjax()) {
            $this->json(['success' => $success, 'message' => $message],
                $success ? 200 : 400);
        } else {
            $this->redirect('/public/index.php?route=admin/dashboard');
        }
    }
}