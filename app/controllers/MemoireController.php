<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/Memoire.php';

class MemoireController extends Controller {

    private Memoire $memoireModel;

    public function __construct() {
        $this->memoireModel = new Memoire();
    }

    // GET : liste des mémoires (selon le rôle)
    public function index(): void {
        $this->requireAuth();
        $user = $_SESSION['user'];

        switch ($user['role']) {
            case 'etudiant':
                $memoires = $this->memoireModel->getParEtudiant($user['idUser']);
                break;
            case 'professeur':
                $memoires = $this->memoireModel->getParProfesseur($user['idUser']);
                break;
            case 'directeur':
                $memoires = $this->memoireModel->getAllAvecEtudiant();
                break;
            default: // consulteur
                $memoires = $this->memoireModel->getAllAvecEtudiant();
                break;
        }

        // Recherche
        if (!empty($_GET['q'])) {
            $memoires = $this->memoireModel->rechercher($_GET['q']);
        }

        $this->render('memoire/index', [
            'memoires' => $memoires,
            'user'     => $user,
        ]);
    }

    // GET : détail d'un mémoire
    public function afficher(string $id): void {
        $this->requireAuth();

        require_once __DIR__ . '/../models/Commentaire.php';
        require_once __DIR__ . '/../models/Like.php';

        $commentaireModel = new Commentaire();
        $likeModel        = new Like();
        $user             = $_SESSION['user'];
        $idMemoire        = (int)$id;

        $memoire = $this->memoireModel->getComplet($idMemoire);

        if (!$memoire) {
            http_response_code(404);
            die('<h1>Mémoire introuvable</h1>');
        }

        // Commentaires + nb likes par commentaire
        $commentaires = $commentaireModel->getParMemoire($idMemoire);
        foreach ($commentaires as &$c) {
            $c['nb_likes'] = $likeModel->compterCommentaire($c['idCommentaire']);
        }
        unset($c);

        // Likes du mémoire
        $memoire['nb_likes_memoire']    = $likeModel->compterMemoire($idMemoire);
        $memoire['user_liked_memoire']  = $likeModel->aLikeMémoire($user['idUser'], $idMemoire);

        $this->render('memoire/afficher', [
            'memoire'      => $memoire,
            'commentaires' => $commentaires,
            'user'         => $user,
        ]);
    }

    // GET : formulaire de soumission
    public function soumettreForm(): void {
        $this->requireRole('etudiant');
        $user = $_SESSION['user'];

        // Vérifie la limite de 2 mémoires
        $nb = $this->memoireModel->compterParEtudiant($user['idUser']);
        if ($nb >= 2) {
            die('<h1>Vous avez déjà soumis 2 mémoires (L3 et M2).</h1>');
        }

        $this->render('memoire/soumettre', ['user' => $user, 'erreurs' => []]);
    }

    // POST : soumet un mémoire
    public function soumettre(): void {
        $this->requireRole('etudiant');
        $user = $_SESSION['user'];

        // Vérifie la limite
        $nb = $this->memoireModel->compterParEtudiant($user['idUser']);
        if ($nb >= 2) {
            die('<h1>Limite atteinte.</h1>');
        }

        // Validation
        $erreurs = [];
        $titre   = $this->input('titre');
        $theme   = $this->input('theme');
        $centre  = $this->input('centre');
        $nbPages = (int)$this->input('nbPages');

        if (empty($titre))   $erreurs[] = 'Le titre est obligatoire.';
        if (empty($theme))   $erreurs[] = 'Le thème est obligatoire.';
        if ($nbPages <= 0)   $erreurs[] = 'Le nombre de pages doit être positif.';

        // Gestion du fichier PDF
        $fichierNom = null;
        if (!empty($_FILES['fichier']['name'])) {
            $ext = strtolower(pathinfo($_FILES['fichier']['name'], PATHINFO_EXTENSION));
            if ($ext !== 'pdf') {
                $erreurs[] = 'Le fichier doit être un PDF.';
            } else {
                $fichierNom = uniqid('memo_') . '.pdf';
                $destination = __DIR__ . '/../../public/uploads/' . $fichierNom;
                if (!move_uploaded_file($_FILES['fichier']['tmp_name'], $destination)) {
                    $erreurs[] = 'Erreur lors de l\'upload du fichier.';
                }
            }
        }

        if (!empty($erreurs)) {
            $this->render('memoire/soumettre', ['user' => $user, 'erreurs' => $erreurs]);
            return;
        }

        // Insertion
        $this->memoireModel->create([
            'titre'            => $titre,
            'theme'            => $theme,
            'nbPages'          => $nbPages,
            'centre'           => $centre,
            'date_soumission'  => date('Y-m-d'),
            'annee_academique' => date('Y'),
            'statut'           => 'en_attente',
            'fichier'          => $fichierNom,
            'idEtudiant'       => $user['idUser'],
        ]);

        $this->redirect('index.php?url=memoire');
    }

    // POST : valider un mémoire (DirecteurEtudes)
    public function valider(string $id): void {
        $this->requireRole('directeur');
        $this->memoireModel->changerStatut((int)$id, 'valide', $_SESSION['user']['idUser']);
        $this->redirect('index.php?url=memoire');
    }

    // POST : rejeter un mémoire (DirecteurEtudes)
    public function rejeter(string $id): void {
        $this->requireRole('directeur');
        $this->memoireModel->changerStatut((int)$id, 'rejete', $_SESSION['user']['idUser']);
        $this->redirect('index.php?url=memoire');
    }

// GET : affiche le PDF dans le navigateur (sans téléchargement)
public function lire(string $id): void {
    $this->requireAuth();

    $memoire = $this->memoireModel->find((int)$id);

    if (!$memoire || empty($memoire['fichier'])) {
        http_response_code(404);
        die('<h1>Fichier introuvable</h1>');
    }

    $fichier = __DIR__ . '/../../public/uploads/' . $memoire['fichier'];

    if (!file_exists($fichier)) {
        http_response_code(404);
        die('<h1>Fichier introuvable sur le serveur</h1>');
    }

    // inline = afficher dans le navigateur
    // attachment = forcer le téléchargement
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="memoire.pdf"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('X-Content-Type-Options: nosniff');
    readfile($fichier);
    exit;
}
}