<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/Commentaire.php';

class CommentaireController extends Controller {

    private Commentaire $commentaire;

    public function __construct() {
        $this->commentaire = new Commentaire();
    }

    // -------------------------------------------------------
    // POST /index.php?route=commentaire/ajouter
    // Accessible à tout utilisateur connecté
    // -------------------------------------------------------
    public function ajouter(): void {
        $this->requiertConnexion();

        $idMemoire = (int) $this->post('idMemoire');
        $contenu   = trim($this->post('contenu'));

        if (!$idMemoire || empty($contenu)) {
            $this->repondre(false, "Le commentaire ne peut pas être vide.", $idMemoire);
            return;
        }

        if (strlen($contenu) > 1000) {
            $this->repondre(false, "Le commentaire ne peut pas dépasser 1000 caractères.", $idMemoire);
            return;
        }

        $idCommentaire = $this->commentaire->ajouter(
            $_SESSION['idUser'],
            $idMemoire,
            $contenu
        );

        if ($this->isAjax()) {
            $this->json([
                'success'       => true,
                'message'       => "Commentaire ajouté.",
                'idCommentaire' => $idCommentaire,
                'contenu'       => htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8'),
                'nom_auteur'    => $_SESSION['name'],
                'date_comment'  => date('d/m/Y H:i'),
                'estModifie'    => false,
                'nb_likes'      => 0,
            ]);
        } else {
            $this->redirect('/public/index.php?route=memoire/detail&id=' . $idMemoire);
        }
    }

    // -------------------------------------------------------
    // POST /index.php?route=commentaire/modifier
    // Accessible uniquement à l'auteur du commentaire
    // -------------------------------------------------------
    public function modifier(): void {
        $this->requiertConnexion();

        $idCommentaire = (int) $this->post('idCommentaire');
        $contenu       = trim($this->post('contenu'));
        $idMemoire     = (int) $this->post('idMemoire');

        if (!$idCommentaire || empty($contenu)) {
            $this->repondre(false, "Données invalides.", $idMemoire);
            return;
        }

        if (strlen($contenu) > 1000) {
            $this->repondre(false, "Le commentaire ne peut pas dépasser 1000 caractères.", $idMemoire);
            return;
        }

        // Vérifier que c'est bien l'auteur
        if (!$this->commentaire->estAuteur($idCommentaire, $_SESSION['idUser'])) {
            $this->repondre(false, "Vous n'êtes pas autorisé à modifier ce commentaire.", $idMemoire);
            return;
        }

        $ok = $this->commentaire->modifier(
            $idCommentaire,
            $_SESSION['idUser'],
            $contenu
        );

        if ($this->isAjax()) {
            $this->json([
                'success'   => $ok,
                'message'   => $ok ? "Commentaire modifié." : "Impossible de modifier ce commentaire.",
                'contenu'   => $ok ? htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8') : null,
                'estModifie' => true,
            ]);
        } else {
            $this->redirect('/public/index.php?route=memoire/detail&id=' . $idMemoire);
        }
    }

    // -------------------------------------------------------
    // POST /index.php?route=commentaire/supprimer
    // Auteur → supprime son commentaire
    // directeur_etudes → peut supprimer n'importe lequel
    // -------------------------------------------------------
    public function supprimer(): void {
        $this->requiertConnexion();

        $idCommentaire = (int) $this->post('idCommentaire');
        $idMemoire     = (int) $this->post('idMemoire');

        if (!$idCommentaire) {
            $this->repondre(false, "Commentaire introuvable.", $idMemoire);
            return;
        }

        $estAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'directeur_etudes';

        // Si pas admin, vérifier ownership
        if (!$estAdmin && !$this->commentaire->estAuteur($idCommentaire, $_SESSION['idUser'])) {
            $this->repondre(false, "Vous n'êtes pas autorisé à supprimer ce commentaire.", $idMemoire);
            return;
        }

        $ok = $this->commentaire->supprimer($idCommentaire, $_SESSION['idUser'], $estAdmin);

        if ($this->isAjax()) {
            $this->json([
                'success' => $ok,
                'message' => $ok
                    ? "Commentaire supprimé."
                    : "Impossible de supprimer ce commentaire.",
            ]);
        } else {
            $this->redirect('/public/index.php?route=memoire/detail&id=' . $idMemoire);
        }
    }

    // -------------------------------------------------------
    // Helper : répondre en JSON ou rediriger selon le contexte
    // -------------------------------------------------------
    private function repondre(bool $success, string $message, int $idMemoire = 0): void {
        if ($this->isAjax()) {
            $this->json(['success' => $success, 'message' => $message],
                $success ? 200 : 400);
        } else {
            if ($idMemoire) {
                $this->redirect('/public/index.php?route=memoire/detail&id=' . $idMemoire);
            } else {
                $this->redirect('/public/index.php?route=memoires');
            }
        }
    }
}