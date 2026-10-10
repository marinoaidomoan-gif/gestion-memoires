<?php

require_once __DIR__ . '/User.php';

class DirecteurEtudes extends User {

    // -------------------------------------------------------
    // Créer un compte DirecteurEtudes
    // Insère dans users + directeuretudes
    // -------------------------------------------------------
    public function creerCompteDirecteur(array $data): int {
        $data['role'] = 'directeur_etudes';
        $idUser = $this->creerCompte($data);

        $stmt = $this->db->prepare(
            "INSERT INTO directeuretudes (idUser, bureau) VALUES (?, ?)"
        );
        $stmt->execute([$idUser, $data['bureau']]);

        return $idUser;
    }

    // -------------------------------------------------------
    // Valider un mémoire
    // -------------------------------------------------------
    public function validerMemo(int $idMemoire): bool {
        $stmt = $this->db->prepare(
            "UPDATE memoire
             SET statut = 'valide', idDirecteur = ?
             WHERE idMemoire = ? AND statut = 'en_attente'"
        );
        return $stmt->execute([$_SESSION['idUser'], $idMemoire]);
    }

    // -------------------------------------------------------
    // Rejeter un mémoire
    // -------------------------------------------------------
    public function rejeterMemo(int $idMemoire): bool {
        $stmt = $this->db->prepare(
            "UPDATE memoire
             SET statut = 'rejete', idDirecteur = ?
             WHERE idMemoire = ? AND statut = 'en_attente'"
        );
        return $stmt->execute([$_SESSION['idUser'], $idMemoire]);
    }

    // -------------------------------------------------------
    // Uploader un mémoire existant (ancien mémoire archivé)
    // -------------------------------------------------------
    public function upload(array $data, array $fichier): int|false {
        $extensionsAutorisees = ['pdf', 'doc', 'docx'];
        $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $extensionsAutorisees)) return false;

        $nomFichier  = 'upload_' . time() . '.' . $extension;
        $destination = __DIR__ . '/../../public/uploads/' . $nomFichier;

        if (!move_uploaded_file($fichier['tmp_name'], $destination)) return false;

        $stmt = $this->db->prepare(
            "INSERT INTO memoire
                (titre, theme, nbPages, centre, date_soumission,
                 annee_academique, statut, fichier, idEtudiant, idDirecteur)
             VALUES (?, ?, ?, ?, ?, ?, 'valide', ?, ?, ?)"
        );
        $stmt->execute([
            $data['titre'],
            $data['theme'],
            $data['nbPages']         ?? null,
            $data['centre']          ?? null,
            $data['date_soumission'] ?? date('Y-m-d'),
            $data['annee_academique'],
            $nomFichier,
            $data['idEtudiant'],
            $_SESSION['idUser'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    // -------------------------------------------------------
    // Créer un compte pour n'importe quel rôle
    // -------------------------------------------------------
    public function creerCompteUtilisateur(array $data): int|false {
        return match($data['role']) {
            'etudiant_diplome'    => (new EtudiantDiplome())->creerCompteEtudiant($data),
            'etudiant_consulteur' => (new EtudiantConsulteur())->creerCompteConsulteur($data),
            'professeur'          => (new Professeur())->creerCompteProfesseur($data),
            'directeur_etudes'    => $this->creerCompteDirecteur($data),
            default               => false,
        };
    }

    // -------------------------------------------------------
    // Assigner un professeur encadrant à un mémoire
    // -------------------------------------------------------
    public function assignerProfesseur(int $idMemoire, int $idProfesseur): bool {
        $stmt = $this->db->prepare(
            "UPDATE memoire SET idProfesseur = ? WHERE idMemoire = ?"
        );
        return $stmt->execute([$idProfesseur, $idMemoire]);
    }

    // -------------------------------------------------------
    // Mémoires en attente (tableau de bord directeur)
    // -------------------------------------------------------
    public function getMemoiresEnAttente() {
        $query = "SELECT m.*, u.name AS nom_etudiant, up.name AS nom_professeur
                FROM memoire m
                LEFT JOIN users u ON m.idEtudiant = u.id_user
                LEFT JOIN users up ON m.idProfesseur = up.id_user
                WHERE m.statut = 'en_attente'
                ORDER BY m.date_soumission ASC";
                
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    // -------------------------------------------------------
    // Gérer les utilisateurs — liste complète
    // -------------------------------------------------------
    public function gererUsers(): array {
        return $this->getAllWithDetails();
    }

    // -------------------------------------------------------
    // Supprimer un commentaire (modération)
    // -------------------------------------------------------
    public function supprimerCommentaire(int $idCommentaire): bool {
        $stmt = $this->db->prepare(
            "DELETE FROM commentaire WHERE idCommentaire = ?"
        );
        return $stmt->execute([$idCommentaire]);
    }

    // -------------------------------------------------------
    // Profil complet du directeur connecté
    // -------------------------------------------------------
    public function getMonProfil(): ?array {
        $stmt = $this->db->prepare(
            "SELECT u.*, de.bureau
            FROM users u
            JOIN directeuretudes de ON u.id_user = de.idUser
            WHERE u.id_user = ?"
        );
        $stmt->execute([$_SESSION['idUser']]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}