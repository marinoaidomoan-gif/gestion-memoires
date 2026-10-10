<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/Professeur.php';
require_once __DIR__ . '/../models/Memoire.php';
require_once __DIR__ . '/../models/Commentaire.php';

class ProfesseurController extends Controller {

    private Professeur $professeur;
    private Memoire    $memoireModel;

    public function __construct() {
        $this->professeur   = new Professeur();
        $this->memoireModel = new Memoire();
    }

    // -------------------------------------------------------
    // GET /index.php?route=professeur/dashboard
    // Mémoires encadrés + stats
    // -------------------------------------------------------
        public function dashboard(): void {
        $idProfesseur = $_SESSION['user']['id'] ?? null;
        
        $mesMemoires = $this->professeur->getMesMemoires($idProfesseur);
        if (!$mesMemoires) {
            $mesMemoires = [];
        }

        // --- GÉNÉRATION DYNAMIQUE DES STATISTIQUES POUR LA VUE ---
        $stats = [
            'en_attente' => 0,
            'valide'     => 0,
            'rejete'     => 0,
            'total'      => count($mesMemoires)
        ];

        // On parcourt les mémoires pour alimenter les compteurs selon le statut exact en BDD
        foreach ($mesMemoires as $m) {
            $statut = $m['statut'] ?? '';
            if ($statut === 'en_attente') {
                $stats['en_attente']++;
            } elseif ($statut === 'valide') {
                $stats['valide']++;
            } elseif ($statut === 'rejete' || $statut === 'refuse') {
                $stats['rejete']++;
            }
        }

        $profil = $this->professeur->getMonProfil();

        // Ajout de la variable 'stats' dans le tableau envoyé au render
        $this->render('professeur/dashboard', [
            'mesMemoires' => $mesMemoires,
            'profil'      => $profil,
            'stats'       => $stats
        ]);
    }

    // -------------------------------------------------------
    // GET /index.php?route=professeur/memoire&id=X
    // Détail d'un mémoire encadré + formulaire évaluation
    // -------------------------------------------------------
    public function voirMemoire(): void {
        $this->requiertRole('professeur');

        $id = (int) $this->get('id', 0);
        if (!$id) {
            $this->redirect('/public/index.php?route=professeur/dashboard');
            return;
        }

        $memoire = $this->memoireModel->findById($id);

        // Vérifier que ce mémoire est bien encadré par ce professeur
        if (!$memoire || $memoire['idProfesseur'] !== $_SESSION['idUser']) {
            $this->redirect('/public/index.php?route=professeur/dashboard');
            return;
        }

        $commentaires = (new Commentaire())->findByMemoire($id);

        $this->render('professeur/voir_memoire', [
            'memoire'      => $memoire,
            'commentaires' => $commentaires,
        ]);
    }

    // -------------------------------------------------------
    // POST /index.php?route=professeur/evaluer
    // Valider ou rejeter un mémoire encadré
    // Supporte AJAX et form classique
    // -------------------------------------------------------
    public function evaluer(): void {
        $this->requiertRole('professeur');

        $idMemoire = (int) $this->post('idMemoire');
        $decision  = $this->post('decision');
        $contenu   = trim($this->post('observation'));

        if (!$idMemoire || !in_array($decision, ['valide', 'rejete'])) {
            $this->repondre(false, "Données invalides.");
            return;
        }

        $ok = $this->professeur->evaluerMemo($idMemoire, $decision);

        // Si une observation est fournie, l'enregistrer comme commentaire
        if ($ok && !empty($contenu)) {
            $this->professeur->ajouterObs($idMemoire, $contenu);
        }

        if ($this->isAjax()) {
            $this->json([
                'success' => $ok,
                'message' => $ok
                    ? "Mémoire " . ($decision === 'valide' ? 'validé' : 'rejeté') . " avec succès."
                    : "Action impossible. Vérifiez que vous êtes l'encadrant de ce mémoire.",
                'statut'  => $ok ? $decision : null,
            ]);
        } else {
            $this->redirect('/public/index.php?route=professeur/dashboard');
        }
    }

    // -------------------------------------------------------
    // GET /index.php?route=professeur/profil
    // -------------------------------------------------------
        // GET /index.php?route=professeur/profil
    public function profil(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Récupération des données du profil professeur connecté
        $idProfesseur = $_SESSION['user']['id'] ?? null;
        $profil = $this->professeur->getMonProfil();

        // 2. Récupération des mémoires pour alimenter le tableau de bord en arrière-plan
        $mesMemoires = $this->professeur->getMesMemoires($idProfesseur);
        if (!$mesMemoires) {
            $mesMemoires = [];
        }

        // 3. Calcul dynamique des statistiques ($stats) indispensables à la vue arrière-plan
        $stats = [
            'en_attente' => 0,
            'valide'     => 0,
            'rejete'     => 0,
            'total'      => count($mesMemoires)
        ];

        foreach ($mesMemoires as $m) {
            $statut = $m['statut'] ?? '';
            if ($statut === 'en_attente') {
                $stats['en_attente']++;
            } elseif ($statut === 'valide') {
                $stats['valide']++;
            } elseif ($statut === 'rejete' || $statut === 'refuse') {
                $stats['rejete']++;
            }
        }

        // 4. Envoi complet des variables pour que l'arrière-plan charge sans planter
        $this->render('professeur/profil', [
            'profil'      => $profil,
            'mesMemoires' => $mesMemoires,
            'stats'       => $stats
        ]);
    }

    // -------------------------------------------------------
    // Helper
    // -------------------------------------------------------
    private function repondre(bool $success, string $message): void {
        if ($this->isAjax()) {
            $this->json(['success' => $success, 'message' => $message],
                $success ? 200 : 400);
        } else {
            $this->redirect('/public/index.php?route=professeur/dashboard');
        }
    }
}