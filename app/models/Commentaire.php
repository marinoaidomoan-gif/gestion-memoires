<?php

require_once __DIR__ . '/../../core/Model.php';

class Commentaire extends Model {

    protected $table = 'commentaire';

    // -------------------------------------------------------
    // findById override — PK est idCommentaire
    // -------------------------------------------------------
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT c.*, u.name AS nom_auteur, u.role AS role_auteur
            FROM commentaire c
            LEFT JOIN users u ON u.idUser = c.idUser
            WHERE c.idCommentaire = ?
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // -------------------------------------------------------
    // Tous les commentaires d'un mémoire (avec auteur + nb likes)
    // -------------------------------------------------------
    public function findByMemoire(int $idMemoire): array {
        $stmt = $this->db->prepare("
            SELECT c.*,
                   u.name AS nom_auteur,
                   u.role AS role_auteur,
                   COUNT(l.idLike) AS nb_likes
            FROM commentaire c
            LEFT JOIN users u ON u.idUser = c.idUser
            LEFT JOIN likes l ON l.idCommentaire = c.idCommentaire
            WHERE c.idMemoire = ?
            GROUP BY c.idCommentaire
            ORDER BY c.date_comment ASC
        ");
        $stmt->execute([$idMemoire]);
        return $stmt->fetchAll();
    }

    // -------------------------------------------------------
    // Ajouter un commentaire
    // -------------------------------------------------------
    public function ajouter(int $idUser, int $idMemoire, string $contenu): int {
        return $this->insert([
            'contenu'   => $contenu,
            'idUser'    => $idUser,
            'idMemoire' => $idMemoire,
        ]);
    }

    // -------------------------------------------------------
    // Modifier un commentaire (seulement par son auteur)
    // -------------------------------------------------------
    public function modifier(int $idCommentaire, int $idUser, string $contenu): bool {
        $stmt = $this->db->prepare("
            UPDATE commentaire
            SET contenu = ?, estModifie = 1
            WHERE idCommentaire = ? AND idUser = ?
        ");
        return $stmt->execute([$contenu, $idCommentaire, $idUser]);
    }

    // -------------------------------------------------------
    // Supprimer un commentaire (auteur ou directeur/admin)
    // -------------------------------------------------------
    public function supprimer(int $idCommentaire, int $idUser, bool $estAdmin = false): bool {
        if ($estAdmin) {
            $stmt = $this->db->prepare("
                DELETE FROM commentaire WHERE idCommentaire = ?
            ");
            return $stmt->execute([$idCommentaire]);
        }

        // Auteur seulement
        $stmt = $this->db->prepare("
            DELETE FROM commentaire WHERE idCommentaire = ? AND idUser = ?
        ");
        return $stmt->execute([$idCommentaire, $idUser]);
    }

    // -------------------------------------------------------
    // Vérifier si un user est bien l'auteur du commentaire
    // -------------------------------------------------------
    public function estAuteur(int $idCommentaire, int $idUser): bool {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM commentaire
            WHERE idCommentaire = ? AND idUser = ?
        ");
        $stmt->execute([$idCommentaire, $idUser]);
        return (bool) $stmt->fetchColumn();
    }

    // -------------------------------------------------------
    // Nombre de commentaires pour un mémoire
    // -------------------------------------------------------
    public function countByMemoire(int $idMemoire): int {
        return $this->count(['idMemoire' => $idMemoire]);
    }
}