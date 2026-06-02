<?php

require_once __DIR__ . '/../../core/Model.php';

class Memoire extends Model {

    protected string $table      = 'memoire';
    protected string $primaryKey = 'idMemoire';

    // Tous les mémoires avec infos de l'étudiant
    public function getAllAvecEtudiant(): array {
        $stmt = $this->pdo->query("
            SELECT m.*, u.name AS nom_etudiant, e.filiere, e.niveau
            FROM memoire m
            JOIN etudiant_diplome e ON m.idEtudiant = e.idUser
            JOIN users u ON e.idUser = u.idUser
            ORDER BY m.date_soumission DESC
        ");
        return $stmt->fetchAll();
    }

    // Un mémoire complet avec toutes les infos liées
    public function getComplet(int $id): array|false {
        $stmt = $this->pdo->prepare("
            SELECT
                m.*,
                u.name        AS nom_etudiant,
                e.filiere,
                e.niveau,
                pr.idUser      AS idProf,
                up.name       AS nom_professeur,
                pr.specialite AS prof_specialite,
                d.idUser      AS idDir,
                ud.name       AS nom_directeur
            FROM memoire m
            JOIN etudiant_diplome e  ON m.idEtudiant   = e.idUser
            JOIN users u             ON e.idUser        = u.idUser
            LEFT JOIN professeur pr  ON m.idProfesseur  = pr.idUser
            LEFT JOIN users up       ON pr.idUser       = up.idUser
            LEFT JOIN directeur_etudes d ON m.idDirecteur = d.idUser
            LEFT JOIN users ud       ON d.idUser        = ud.idUser
            WHERE m.idMemoire = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Mémoires d'un étudiant
    public function getParEtudiant(int $idEtudiant): array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM memoire
            WHERE idEtudiant = ?
            ORDER BY date_soumission DESC
        ");
        $stmt->execute([$idEtudiant]);
        return $stmt->fetchAll();
    }

    // Mémoires encadrés par un professeur
    public function getParProfesseur(int $idProfesseur): array {
        $stmt = $this->pdo->prepare("
            SELECT m.*, u.name AS nom_etudiant, e.filiere
            FROM memoire m
            JOIN etudiant_diplome e ON m.idEtudiant = e.idUser
            JOIN users u ON e.idUser = u.idUser
            WHERE m.idProfesseur = ?
            ORDER BY m.date_soumission DESC
        ");
        $stmt->execute([$idProfesseur]);
        return $stmt->fetchAll();
    }

    // Mémoires en attente de validation
    public function getEnAttente(): array {
        return $this->where('statut = ?', ['en_attente']);
    }

    // Changer le statut (valide / rejete)
    public function changerStatut(int $id, string $statut, int $idDirecteur): bool {
        $stmt = $this->pdo->prepare("
            UPDATE memoire
            SET statut = ?, idDirecteur = ?
            WHERE idMemoire = ?
        ");
        return $stmt->execute([$statut, $idDirecteur, $id]);
    }

    // Recherche par titre ou thème
    public function rechercher(string $motCle): array {
        $like = "%$motCle%";
        $stmt = $this->pdo->prepare("
            SELECT m.*, u.name AS nom_etudiant, e.filiere
            FROM memoire m
            JOIN etudiant_diplome e ON m.idEtudiant = e.idUser
            JOIN users u ON e.idUser = u.idUser
            WHERE m.titre LIKE ? OR m.theme LIKE ?
            ORDER BY m.date_soumission DESC
        ");
        $stmt->execute([$like, $like]);
        return $stmt->fetchAll();
    }

    // Vérifie combien de mémoires a soumis un étudiant (max 2)
    public function compterParEtudiant(int $idEtudiant): int {
        return $this->count('idEtudiant = ?', [$idEtudiant]);
    }
}