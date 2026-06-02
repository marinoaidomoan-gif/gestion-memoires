<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/Commentaire.php';

class CommentaireController extends Controller {

    private Commentaire $commentaireModel;

    public function __construct() {
        $this->commentaireModel = new Commentaire();
    }

    // POST : ajouter un commentaire
    public function ajouter(): void {
        $this->requireAuth();
        $user      = $_SESSION['user'];
        $idMemoire = (int)$this->input('idMemoire');
        $contenu   = $this->input('contenu');

        if (empty($contenu)) {
            $this->redirect("index.php?url=memoire/afficher/$idMemoire");
            return;
        }

        $this->commentaireModel->ajouter($user['idUser'], $idMemoire, $contenu);
        $this->redirect("index.php?url=memoire/afficher/$idMemoire");
    }

    // POST : modifier un commentaire
    public function modifier(string $id): void {
        $this->requireAuth();
        $user    = $_SESSION['user'];
        $contenu = $this->input('contenu');
        $idMemoire = (int)$this->input('idMemoire');

        if (!empty($contenu)) {
            $this->commentaireModel->modifierSiAuteur((int)$id, $user['idUser'], $contenu);
        }

        $this->redirect("index.php?url=memoire/afficher/$idMemoire");
    }

    // POST : supprimer un commentaire
    public function supprimer(string $id): void {
        $this->requireAuth();
        $user      = $_SESSION['user'];
        $idMemoire = (int)$this->input('idMemoire');

        // Directeur peut tout supprimer
        if ($user['role'] === 'directeur') {
            $this->commentaireModel->delete((int)$id);
        } else {
            $this->commentaireModel->supprimerSiAuteur((int)$id, $user['idUser']);
        }

        $this->redirect("index.php?url=memoire/afficher/$idMemoire");
    }
}