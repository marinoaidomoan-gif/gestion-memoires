<?php

require_once __DIR__ . '/../../core/Model.php';

class Commentaire extends Model {

    protected string $table      = 'commentaire';
    protected string $primaryKey = 'idCommentaire';

    // Commentaires d'un mémoire avec nom de l'auteur
    public function getParMemoire(int $idMemoire): array {
        $stmt = $this->pdo->prepare("
            SELECT c.*, u.name AS nom_auteur
            FROM commentaire c
            JOIN users u ON c.idUser = u.idUser
            WHERE c.idMemoire = ?
            ORDER BY c.date_comment ASC
        ");
        $stmt->execute([$idMemoire]);
        return $stmt->fetchAll();
    }

    // Ajouter un commentaire
    public function ajouter(int $idUser, int $idMemoire, string $contenu): int {
        return $this->create([
            'contenu'      => $contenu,
            'date_comment' => date('Y-m-d H:i:s'),
            'estModifie'   => 0,
            'idUser'       => $idUser,
            'idMemoire'    => $idMemoire,
        ]);
    }

    // Modifier un commentaire (seulement par son auteur)
    public function modifierSiAuteur(int $id, int $idUser, string $contenu): bool {
        $stmt = $this->pdo->prepare("
            UPDATE commentaire
            SET contenu = ?, estModifie = 1
            WHERE idCommentaire = ? AND idUser = ?
        ");
        return $stmt->execute([$contenu, $id, $idUser]);
    }

    // Supprimer (auteur ou directeur)
    public function supprimerSiAuteur(int $id, int $idUser): bool {
        $stmt = $this->pdo->prepare("
            DELETE FROM commentaire
            WHERE idCommentaire = ? AND idUser = ?
        ");
        return $stmt->execute([$id, $idUser]);
    }

    // Vérifie si l'utilisateur est l'auteur
    public function estAuteur(int $id, int $idUser): bool {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM commentaire
            WHERE idCommentaire = ? AND idUser = ?
        ");
        $stmt->execute([$id, $idUser]);
        return (bool) $stmt->fetchColumn();
    }
}