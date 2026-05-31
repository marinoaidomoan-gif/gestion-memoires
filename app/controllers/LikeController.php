<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/Like.php';

class LikeController extends Controller {

    private Like $like;

    public function __construct() {
        $this->like = new Like();
    }

    // -------------------------------------------------------
    // POST /index.php?route=like/toggle
    // Like/unlike un mémoire ou un commentaire
    // Conçu pour être appelé en AJAX uniquement
    // Accessible à tout utilisateur connecté
    // -------------------------------------------------------
    public function toggle(): void {
        $this->requiertConnexion();

        // Toujours répondre en JSON pour cette route
        $type = $this->post('type'); // 'memoire' ou 'commentaire'
        $id   = (int) $this->post('id');

        if (!$id || !in_array($type, ['memoire', 'commentaire'])) {
            $this->json([
                'success' => false,
                'message' => "Paramètres invalides.",
            ], 400);
            return;
        }

        if ($type === 'memoire') {
            $estLike = $this->like->toggleMemoire($_SESSION['idUser'], $id);
            $nbLikes = $this->like->countByMemoire($id);
        } else {
            $estLike = $this->like->toggleCommentaire($_SESSION['idUser'], $id);
            $nbLikes = $this->like->countByCommentaire($id);
        }

        $this->json([
            'success' => true,
            'liked'   => $estLike,   // true = liké, false = unliké
            'nbLikes' => $nbLikes,
            'message' => $estLike ? "Like ajouté." : "Like retiré.",
        ]);
    }
}