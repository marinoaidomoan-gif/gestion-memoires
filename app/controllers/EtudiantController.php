<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/EtudiantDiplome.php';
require_once __DIR__ . '/../models/EtudiantConsulteur.php';
require_once __DIR__ . '/../models/Memoire.php';
require_once __DIR__ . '/../models/Commentaire.php';
require_once __DIR__ . '/../models/Like.php';

class EtudiantController extends Controller {

    // -------------------------------------------------------
    // GET /index.php?route=etudiant/dashboard
    // Dashboard commun — dispatche selon le rôle exact
    // -------------------------------------------------------
    public function dashboard(): void {
        $this->requiertRoles(['etudiant_diplome', 'etudiant_consulteur']);

        if ($_SESSION['role'] === 'etudiant_diplome') {
            $this->dashboardDiplome();
        } else {
            $this->dashboardConsulteur();
        }
    }

    // -------------------------------------------------------
    // Dashboard EtudiantDiplome
    // Ses mémoires + stats rapides
    // -------------------------------------------------------
    private function dashboardDiplome(): void {
        $etudiant = new EtudiantDiplome();

        $mesMemoires = $etudiant->getMesMemoires();
        $profil      = $etudiant->getMonProfil();

        // Stats rapides
        $stats = [
            'total'      => count($mesMemoires),
            'en_attente' => count(array_filter($mesMemoires, fn($m) => $m['statut'] === 'en_attente')),
            'valide'     => count(array_filter($mesMemoires, fn($m) => $m['statut'] === 'valide')),
            'rejete'     => count(array_filter($mesMemoires, fn($m) => $m['statut'] === 'rejete')),
        ];

        $this->render('etudiant/dashboard_diplome', [
            'mesMemoires' => $mesMemoires,
            'profil'      => $profil,
            'stats'       => $stats,
        ]);
    }

    // -------------------------------------------------------
    // Dashboard EtudiantConsulteur
    // Recherche + mémoires récents
    // -------------------------------------------------------
    private function dashboardConsulteur(): void {
        $consulteur = new EtudiantConsulteur();

        $filtres = [];
        if ($motCle = $this->get('q'))     $filtres['motcle'] = $motCle;
        if ($annee  = $this->get('annee')) $filtres['annee_academique'] = $annee;
        if ($theme  = $this->get('theme')) $filtres['theme'] = $theme;

        $memoires = $consulteur->rechercherMemo($filtres);
        $annees   = $consulteur->getAnnees();
        $profil   = $consulteur->getMonProfil();

        // Mémoires likés par ce consulteur (pour l'état des boutons)
        $like          = new Like();
        $memoiresLikes = $like->getMemoresLikesParUser($_SESSION['idUser']);

        $this->render('etudiant/dashboard_consulteur', [
            'memoires'      => $memoires,
            'annees'        => $annees,
            'profil'        => $profil,
            'memoiresLikes' => $memoiresLikes,
            'filtres'       => $filtres,
        ]);
    }

    // -------------------------------------------------------
    // GET /index.php?route=etudiant/memoire&id=X
    // Détail d'un mémoire + commentaires + likes
    // Accessible aux deux types d'étudiants
    // -------------------------------------------------------
    public function voirMemoire(): void {
        $this->requiertRoles(['etudiant_diplome', 'etudiant_consulteur']);

        $id = (int) $this->get('id', 0);
        if (!$id) {
            $this->redirect('/public/index.php?route=etudiant/dashboard');
            return;
        }

        $memoireModel = new Memoire();
        $memoire      = $memoireModel->findById($id);

        if (!$memoire) {
            $this->redirect('/public/index.php?route=etudiant/dashboard');
            return;
        }

        // Etudiant diplômé : voit uniquement ses propres mémoires non validés
        if ($_SESSION['role'] === 'etudiant_diplome'
            && $memoire['statut'] !== 'valide'
            && $memoire['idEtudiant'] !== $_SESSION['idUser']) {
            $this->redirect('/public/index.php?route=etudiant/dashboard');
            return;
        }

        $commentaireModel = new Commentaire();
        $likeModel        = new Like();

        $commentaires = $commentaireModel->findByMemoire($id);
        $nbLikes      = $likeModel->countByMemoire($id);
        $dejaLike     = $likeModel->aDejaLikeMemoire($_SESSION['idUser'], $id);

        // Likes des commentaires par ce user
        $likesCommentaires = [];
        foreach ($commentaires as $c) {
            $likesCommentaires[$c['idCommentaire']] =
                $likeModel->aDejaLikeCommentaire($_SESSION['idUser'], $c['idCommentaire']);
        }

        $this->render('etudiant/voir_memoire', [
            'memoire'           => $memoire,
            'commentaires'      => $commentaires,
            'nbLikes'           => $nbLikes,
            'dejaLike'          => $dejaLike,
            'likesCommentaires' => $likesCommentaires,
        ]);
    }

    // -------------------------------------------------------
    // GET /index.php?route=etudiant/profil
    // -------------------------------------------------------
    public function profil(): void {
        $this->requiertRoles(['etudiant_diplome', 'etudiant_consulteur']);

        if ($_SESSION['role'] === 'etudiant_diplome') {
            $profil = (new EtudiantDiplome())->getMonProfil();
        } else {
            $profil = (new EtudiantConsulteur())->getMonProfil();
        }

        $this->render('etudiant/profil', ['profil' => $profil]);
    }
}