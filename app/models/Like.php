<?php

require_once __DIR__ . '/../../core/Model.php';

class Like extends Model {

    protected string $table      = 'likes';
    protected string $primaryKey = 'idLike';

    // Compte les likes d'un mémoire
    public function compterMemoire(int $idMemoire): int {
        return $this->count('idMemoire = ?', [$idMemoire]);
    }

    // Compte les likes d'un commentaire
    public function compterCommentaire(int $idCommentaire): int {
        return $this->count('idCommentaire = ?', [$idCommentaire]);
    }

    // L'utilisateur a-t-il déjà liké ce mémoire ?
    public function aLikeMémoire(int $idUser, int $idMemoire): bool {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM likes
            WHERE idUser = ? AND idMemoire = ?
        ");
        $stmt->execute([$idUser, $idMemoire]);
        return (bool) $stmt->fetchColumn();
    }

    // L'utilisateur a-t-il déjà liké ce commentaire ?
    public function aLikeCommentaire(int $idUser, int $idCommentaire): bool {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM likes
            WHERE idUser = ? AND idCommentaire = ?
        ");
        $stmt->execute([$idUser, $idCommentaire]);
        return (bool) $stmt->fetchColumn();
    }

    // Toggle like mémoire (like si pas encore, unlike sinon)
    public function toggleMemoire(int $idUser, int $idMemoire): string {
        if ($this->aLikeMémoire($idUser, $idMemoire)) {
            $stmt = $this->pdo->prepare("
                DELETE FROM likes WHERE idUser = ? AND idMemoire = ?
            ");
            $stmt->execute([$idUser, $idMemoire]);
            return 'unliked';
        } else {
            $this->create([
                'dateLike'    => date('Y-m-d H:i:s'),
                'idUser'      => $idUser,
                'idMemoire'   => $idMemoire,
                'idCommentaire' => null,
            ]);
            return 'liked';
        }
    }

    // Toggle like commentaire
    public function toggleCommentaire(int $idUser, int $idCommentaire): string {
        if ($this->aLikeCommentaire($idUser, $idCommentaire)) {
            $stmt = $this->pdo->prepare("
                DELETE FROM likes WHERE idUser = ? AND idCommentaire = ?
            ");
            $stmt->execute([$idUser, $idCommentaire]);
            return 'unliked';
        } else {
            $this->create([
                'dateLike'      => date('Y-m-d H:i:s'),
                'idUser'        => $idUser,
                'idMemoire'     => null,
                'idCommentaire' => $idCommentaire,
            ]);
            return 'liked';
        }
    }
}