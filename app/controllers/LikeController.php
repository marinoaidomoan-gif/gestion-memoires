<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/Like.php';

class LikeController extends Controller {

    private Like $likeModel;

    public function __construct() {
        $this->likeModel = new Like();
    }

    // POST : toggle like (réponse JSON pour AJAX)
    // Params attendus : type (memoire|commentaire), id (int)
    public function toggle(): void {
        $this->requireAuth();
        $user = $_SESSION['user'];

        $type = $_POST['type'] ?? '';
        $id   = (int)($_POST['id'] ?? 0);

        if (!$id || !in_array($type, ['memoire', 'commentaire'])) {
            $this->json(['error' => 'Paramètres invalides.'], 400);
        }

        if ($type === 'memoire') {
            $action = $this->likeModel->toggleMemoire($user['idUser'], $id);
            $count  = $this->likeModel->compterMemoire($id);
        } else {
            $action = $this->likeModel->toggleCommentaire($user['idUser'], $id);
            $count  = $this->likeModel->compterCommentaire($id);
        }

        $this->json(['action' => $action, 'count' => $count]);
    }
}