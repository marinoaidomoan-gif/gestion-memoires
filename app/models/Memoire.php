<?php

require_once __DIR__ . '/../../core/Model.php';

class Memoire extends Model {

    protected $table = 'memoire';

    // Statuts possibles
    const STATUT_ATTENTE = 'en_attente';
    const STATUT_VALIDE  = 'valide';
    const STATUT_REJETE  = 'rejete';

    // -------------------------------------------------------
    // findById override — PK est idMemoire (casse différente)
    // -------------------------------------------------------
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT m.*,
                   u.name AS nom_etudiant,
                   p.name AS nom_professeur,
                   p2.name AS nom_directeur
            FROM memoire m
            LEFT JOIN users u  ON u.idUser  = m.idEtudiant
            LEFT JOIN users p  ON p.idUser  = m.idProfesseur
            LEFT JOIN users p2 ON p2.idUser = m.idDirecteur
            WHERE m.idMemoire = ?
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // -------------------------------------------------------
    // Tous les mémoires avec infos auteur/encadrant
    // -------------------------------------------------------
    public function findAll() {
        $query = "
            SELECT m.*,
                   u.name AS nom_etudiant,
                   p.name AS nom_professeur,
                   p2.name AS nom_directeur
            FROM memoire m
            LEFT JOIN users u ON u.id_user = m.idEtudiant
            LEFT JOIN users p ON p.id_user = m.idProfesseur
            LEFT JOIN users p2 ON p2.id_user = m.idDirecteur
            ORDER BY m.date_soumission DESC
        ";
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    // -------------------------------------------------------
    // Mémoires validés uniquement (accès public/consulteurs)
    // -------------------------------------------------------
    public function findValides() {
        $query = "
            SELECT m.*,
                CONCAT(u.prenom, ' ', u.name) AS nom_etudiant,
                CONCAT(p.prenom, ' ', p.name) AS nom_professeur
            FROM memoire m
            LEFT JOIN users u ON u.id_user = m.idEtudiant
            LEFT JOIN users p ON p.id_user = m.idProfesseur
            WHERE m.statut = 'valide'
            ORDER BY m.date_soumission DESC
        ";
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    // -------------------------------------------------------
    // Mémoires d'un étudiant précis
    // -------------------------------------------------------
    public function findByEtudiant(int $idEtudiant): array {
        $stmt = $this->db->prepare("
            SELECT m.*,
                   p.name  AS nom_professeur,
                   p2.name AS nom_directeur
            FROM memoire m
            LEFT JOIN users p  ON p.idUser  = m.idProfesseur
            LEFT JOIN users p2 ON p2.idUser = m.idDirecteur
            WHERE m.idEtudiant = ?
            ORDER BY m.date_soumission DESC
        ");
        $stmt->execute([$idEtudiant]);
        return $stmt->fetchAll();
    }

    // -------------------------------------------------------
    // Mémoires encadrés par un professeur
    // -------------------------------------------------------
    public function findByProfesseur(int $idProfesseur): array {
        $stmt = $this->db->prepare("
            SELECT m.*,
                   u.name AS nom_etudiant
            FROM memoire m
            LEFT JOIN users u ON u.idUser = m.idEtudiant
            WHERE m.idProfesseur = ?
            ORDER BY m.date_soumission DESC
        ");
        $stmt->execute([$idProfesseur]);
        return $stmt->fetchAll();
    }

    // -------------------------------------------------------
    // Mémoires en attente (pour le DirecteurEtudes)
    // -------------------------------------------------------
    public function findEnAttente(): array {
        return $this->findWhere(['statut' => self::STATUT_ATTENTE]);
    }

    // -------------------------------------------------------
    // Recherche par titre ou thème (LIKE)
    // -------------------------------------------------------
    public function rechercher(string $motCle): array {
        $like = '%' . $motCle . '%';
        $stmt = $this->db->prepare("
            SELECT m.*,
                   u.name AS nom_etudiant,
                   p.name AS nom_professeur
            FROM memoire m
            LEFT JOIN users u ON u.idUser = m.idEtudiant
            LEFT JOIN users p ON p.idUser = m.idProfesseur
            WHERE m.statut = 'valide'
              AND (m.titre LIKE ? OR m.theme LIKE ?)
            ORDER BY m.date_soumission DESC
        ");
        $stmt->execute([$like, $like]);
        return $stmt->fetchAll();
    }

    // -------------------------------------------------------
    // Changer le statut (valider ou rejeter)
    // -------------------------------------------------------
    public function changerStatut(int $idMemoire, string $statut, int $idDirecteur): bool {
        if (!in_array($statut, [self::STATUT_VALIDE, self::STATUT_REJETE])) {
            return false;
        }
        $stmt = $this->db->prepare("
            UPDATE memoire
            SET statut = ?, idDirecteur = ?
            WHERE idMemoire = ?
        ");
        return $stmt->execute([$statut, $idDirecteur, $idMemoire]);
    }

    // -------------------------------------------------------
    // Assigner un professeur encadrant
    // -------------------------------------------------------
    public function assignerProfesseur(int $idMemoire, int $idProfesseur): bool {
        $stmt = $this->db->prepare("
            UPDATE memoire SET idProfesseur = ? WHERE idMemoire = ?
        ");
        return $stmt->execute([$idProfesseur, $idMemoire]);
    }

    // -------------------------------------------------------
    // Soumettre un mémoire (insert + retourne l'id)
    // -------------------------------------------------------
    public function soumettre(array $data): int {
        // S'assurer que le statut initial est 'en_attente'
        $data['statut'] = self::STATUT_ATTENTE;
        $data['date_soumission'] = $data['date_soumission'] ?? date('Y-m-d');
        return $this->insert($data);
    }

    // -------------------------------------------------------
    // Nombre de mémoires par statut (stats dashboard)
    // -------------------------------------------------------
    public function countParStatut(): array {
        $stmt = $this->db->query("
            SELECT statut, COUNT(*) AS total
            FROM memoire
            GROUP BY statut
        ");
        $result = ['en_attente' => 0, 'valide' => 0, 'rejete' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['statut']] = (int) $row['total'];
        }
        return $result;
    }

    public function getAnnees() {
        $query = "SELECT DISTINCT YEAR(date_soumission) AS annee FROM memoire WHERE date_soumission IS NOT NULL ORDER BY annee DESC";
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Méthode requise par le contrôleur pour charger la liste des enseignants
    public function getProfesseurs(): array {
        $query = "SELECT id_user, name, prenom FROM users WHERE role = 'professeur' ORDER BY name ASC";
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

}