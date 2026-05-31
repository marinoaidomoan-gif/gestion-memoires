<?php

require_once __DIR__ . '/User.php';

class EtudiantConsulteur extends User {

    // -------------------------------------------------------
    // Créer un compte EtudiantConsulteur
    // Insère dans users + etudiantconsulteur
    // -------------------------------------------------------
    public function creerCompteConsulteur(array $data): int {
        $data['role'] = 'etudiant_consulteur';
        $idUser = $this->creerCompte($data);

        $stmt = $this->db->prepare(
            "INSERT INTO etudiantconsulteur (idUser, niveau, filiere)
             VALUES (?, ?, ?)"
        );
        $stmt->execute([
            $idUser,
            $data['niveau'],
            $data['filiere'],
        ]);

        return $idUser;
    }

    // -------------------------------------------------------
    // Consulter un mémoire validé
    // -------------------------------------------------------
    public function consulterMemo(int $idMemoire): ?array {
        $stmt = $this->db->prepare(
            "SELECT m.*,
                    u.name AS nom_etudiant,
                    up.name AS nom_professeur,
                    (SELECT COUNT(*) FROM likes l WHERE l.idMemoire = m.idMemoire)
                        AS nb_likes,
                    (SELECT COUNT(*) FROM commentaire c WHERE c.idMemoire = m.idMemoire)
                        AS nb_commentaires
             FROM memoire m
             LEFT JOIN users u  ON m.idEtudiant   = u.idUser
             LEFT JOIN users up ON m.idProfesseur = up.idUser
             WHERE m.idMemoire = ? AND m.statut = 'valide'"
        );
        $stmt->execute([$idMemoire]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // -------------------------------------------------------
    // Rechercher des mémoires avec filtres
    // -------------------------------------------------------
    public function rechercherMemo(array $filtres = []): array {
        $sql = "SELECT m.*, u.name AS nom_etudiant,
                       (SELECT COUNT(*) FROM likes l WHERE l.idMemoire = m.idMemoire) AS nb_likes,
                       (SELECT COUNT(*) FROM commentaire c WHERE c.idMemoire = m.idMemoire) AS nb_commentaires
                FROM memoire m
                LEFT JOIN users u ON m.idEtudiant = u.idUser
                WHERE m.statut = 'valide'";

        $params = [];

        if (!empty($filtres['annee_academique'])) {
            $sql .= " AND m.annee_academique = ?";
            $params[] = $filtres['annee_academique'];
        }

        if (!empty($filtres['theme'])) {
            $sql .= " AND m.theme LIKE ?";
            $params[] = '%' . $filtres['theme'] . '%';
        }

        if (!empty($filtres['motcle'])) {
            $sql .= " AND (m.titre LIKE ? OR m.theme LIKE ?)";
            $params[] = '%' . $filtres['motcle'] . '%';
            $params[] = '%' . $filtres['motcle'] . '%';
        }

        $sql .= " ORDER BY m.date_soumission DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // -------------------------------------------------------
    // Profil complet du consulteur connecté
    // -------------------------------------------------------
    public function getMonProfil(): ?array {
        $stmt = $this->db->prepare(
            "SELECT u.*, ec.niveau, ec.filiere
             FROM users u
             JOIN etudiantconsulteur ec ON u.idUser = ec.idUser
             WHERE u.idUser = ?"
        );
        $stmt->execute([$_SESSION['idUser']]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // -------------------------------------------------------
    // Années académiques disponibles (pour les filtres)
    // -------------------------------------------------------
    public function getAnnees(): array {
        $stmt = $this->db->query(
            "SELECT DISTINCT annee_academique
             FROM memoire WHERE statut = 'valide'
             ORDER BY annee_academique DESC"
        );
        return $stmt->fetchAll();
    }
}