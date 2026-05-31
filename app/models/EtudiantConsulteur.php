<?php

require_once __DIR__ . '/User.php';

class EtudiantConsulteur extends User {

    // -------------------------------------------------------
    // Créer un compte EtudiantConsulteur complet
    // Insère dans users + etudiantconsulteur
    // -------------------------------------------------------
    public function creerCompteConsulteur(array $data): int {
        // 1. Insérer dans users
        $data['role'] = 'etudiant_consulteur';
        $idUser = $this->creerCompte($data);

        // 2. Insérer dans etudiantconsulteur
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
    // Consulter un mémoire (lecture seule en ligne)
    // Accessible uniquement si connecté
    // -------------------------------------------------------
    public function consulterMemo(int $idMemoire): ?array {
        $stmt = $this->db->prepare(
            "SELECT m.*,
                    u.name    AS nomEtudiant,
                    u.prenom  AS prenomEtudiant,
                    ed.filiere AS filiereEtudiant,
                    up.name   AS nomProfesseur,
                    up.prenom AS prenomProfesseur,
                    (SELECT COUNT(*) FROM likes l WHERE l.idMemoire = m.idMemoire)
                        AS nbLikes,
                    (SELECT COUNT(*) FROM commentaire c WHERE c.idMemoire = m.idMemoire)
                        AS nbCommentaires
             FROM memoire m
             JOIN users u              ON m.idEtudiant  = u.idUser
             JOIN etudiantdiplome ed   ON m.idEtudiant  = ed.idUser
             LEFT JOIN users up        ON m.idProfesseur = up.idUser
             WHERE m.idMemoire = ?
             AND m.statut NOT IN ('archive', 'en_attente')"
        );
        $stmt->execute([$idMemoire]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // -------------------------------------------------------
    // Rechercher des mémoires avec filtres
    // -------------------------------------------------------
    public function rechercherMemo(array $filtres = []): array {
        $sql = "SELECT m.*,
                       u.name   AS nomEtudiant,
                       u.prenom AS prenomEtudiant,
                       ed.filiere,
                       (SELECT COUNT(*) FROM likes l WHERE l.idMemoire = m.idMemoire)
                           AS nbLikes,
                       (SELECT COUNT(*) FROM commentaire c WHERE c.idMemoire = m.idMemoire)
                           AS nbCommentaires
                FROM memoire m
                JOIN users u            ON m.idEtudiant = u.idUser
                JOIN etudiantdiplome ed ON m.idEtudiant = ed.idUser
                WHERE m.statut NOT IN ('archive', 'en_attente')";

        $params = [];

        // Filtre par filière
        if (!empty($filtres['filiere'])) {
            $sql .= " AND ed.filiere = ?";
            $params[] = $filtres['filiere'];
        }

        // Filtre par année académique
        if (!empty($filtres['annee_academique'])) {
            $sql .= " AND m.annee_academique = ?";
            $params[] = $filtres['annee_academique'];
        }

        // Filtre par thème
        if (!empty($filtres['theme'])) {
            $sql .= " AND m.theme LIKE ?";
            $params[] = '%' . $filtres['theme'] . '%';
        }

        // Filtre par mot-clé (titre ou thème)
        if (!empty($filtres['motcle'])) {
            $sql .= " AND (m.titre LIKE ? OR m.theme LIKE ?)";
            $params[] = '%' . $filtres['motcle'] . '%';
            $params[] = '%' . $filtres['motcle'] . '%';
        }

        // Filtre par statut
        if (!empty($filtres['statut'])) {
            $sql .= " AND m.statut = ?";
            $params[] = $filtres['statut'];
        }

        $sql .= " ORDER BY m.date_soumission DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // -------------------------------------------------------
    // Récupérer les commentaires d'un mémoire
    // -------------------------------------------------------
    public function getCommentairesMemoire(int $idMemoire): array {
        $stmt = $this->db->prepare(
            "SELECT c.*,
                    u.name   AS nomAuteur,
                    u.prenom AS prenomAuteur
             FROM commentaire c
             JOIN users u ON c.idUser = u.idUser
             WHERE c.idMemoire = ?
             ORDER BY c.date_comment ASC"
        );
        $stmt->execute([$idMemoire]);
        return $stmt->fetchAll();
    }

    // -------------------------------------------------------
    // Récupérer les infos complètes du consulteur connecté
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
    // Récupérer toutes les filières disponibles
    // (pour alimenter les filtres de recherche)
    // -------------------------------------------------------
    public function getFilieres(): array {
        $stmt = $this->db->query(
            "SELECT DISTINCT filiere
             FROM etudiantdiplome
             ORDER BY filiere ASC"
        );
        return $stmt->fetchAll();
    }

    // -------------------------------------------------------
    // Récupérer toutes les années disponibles
    // (pour alimenter les filtres de recherche)
    // -------------------------------------------------------
    public function getAnnees(): array {
        $stmt = $this->db->query(
            "SELECT DISTINCT annee_academique
             FROM memoire
             WHERE statut NOT IN ('archive', 'en_attente')
             ORDER BY annee_academique DESC"
        );
        return $stmt->fetchAll();
    }
}