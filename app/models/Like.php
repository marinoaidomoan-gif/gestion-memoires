<?php

require_once __DIR__ . '/../../core/Model.php';

class Like extends Model {

    protected $table = 'like';

    // -------------------------------------------------------
    // Liker un mémoire (toggle : like/unlike)
    // Retourne true si liké, false si unliké
    // -------------------------------------------------------
    public function toggleMemoire(int $idUser, int $idMemoire): bool {
        if ($this->aDejaLikeMemoire($idUser, $idMemoire)) {
            $this->unlikeMemoire($idUser, $idMemoire);
            return false; // unliké
        }
        $this->insert([
            'idUser'    => $idUser,
            'idMemoire' => $idMemoire,
        ]);
        return true; // liké
    }

    // -------------------------------------------------------
    // Liker un commentaire (toggle)
    // Retourne true si liké, false si unliké
    // -------------------------------------------------------
    public function toggleCommentaire(int $idUser, int $idCommentaire): bool {
        if ($this->aDejaLikeCommentaire($idUser, $idCommentaire)) {
            $this->unlikeCommentaire($idUser, $idCommentaire);
            return false;
        }
        $this->insert([
            'idUser'         => $idUser,
            'idCommentaire'  => $idCommentaire,
        ]);
        return true;
    }

    // -------------------------------------------------------
    // Vérifier si un user a déjà liké un mémoire
    // -------------------------------------------------------
    public function aDejaLikeMemoire(int $idUser, int $idMemoire): bool {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM likes
            WHERE idUser = ? AND idMemoire = ?
        ");
        $stmt->execute([$idUser, $idMemoire]);
        return (bool) $stmt->fetchColumn();
    }

    // -------------------------------------------------------
    // Vérifier si un user a déjà liké un commentaire
    // -------------------------------------------------------
    public function aDejaLikeCommentaire(int $idUser, int $idCommentaire): bool {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM likes
            WHERE idUser = ? AND idCommentaire = ?
        ");
        $stmt->execute([$idUser, $idCommentaire]);
        return (bool) $stmt->fetchColumn();
    }

    // -------------------------------------------------------
    // Nombre de likes d'un mémoire
    // -------------------------------------------------------
    public function countByMemoire(int $idMemoire): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM likes WHERE idMemoire = ?
        ");
        $stmt->execute([$idMemoire]);
        return (int) $stmt->fetchColumn();
    }

    // -------------------------------------------------------
    // Nombre de likes d'un commentaire
    // -------------------------------------------------------
    public function countByCommentaire(int $idCommentaire): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM likes WHERE idCommentaire = ?
        ");
        $stmt->execute([$idCommentaire]);
        return (int) $stmt->fetchColumn();
    }

    // -------------------------------------------------------
    // Récupérer les likes d'un user sur une liste de mémoires
    // Utile pour afficher l'état des boutons like en vue liste
    // Retourne un tableau d'idMemoire likés par le user
    // -------------------------------------------------------
    public function getMemoresLikesParUser(int $idUser): array {
        $stmt = $this->db->prepare("
            SELECT idMemoire FROM likes
            WHERE idUser = ? AND idMemoire IS NOT NULL
        ");
        $stmt->execute([$idUser]);
        return array_column($stmt->fetchAll(), 'idMemoire');
    }

    // -------------------------------------------------------
    // Méthodes privées : unlike direct
    // -------------------------------------------------------
    private function unlikeMemoire(int $idUser, int $idMemoire): void {
        $stmt = $this->db->prepare("
            DELETE FROM likes WHERE idUser = ? AND idMemoire = ?
        ");
        $stmt->execute([$idUser, $idMemoire]);
    }

    private function unlikeCommentaire(int $idUser, int $idCommentaire): void {
        $stmt = $this->db->prepare("
            DELETE FROM likes WHERE idUser = ? AND idCommentaire = ?
        ");
        $stmt->execute([$idUser, $idCommentaire]);
    }
}