<?php

require_once __DIR__ . '/User.php';

class DirecteurEtudes extends User {

    // -------------------------------------------------------
    // Créer un compte DirecteurEtudes complet
    // Insère dans users + directeuretudes
    // -------------------------------------------------------
    public function creerCompteDirecteur(array $data): int {
        // 1. Insérer dans users
        $data['role'] = 'directeur_etudes';
        $idUser = $this->creerCompte($data);

        // 2. Insérer dans directeuretudes
        $stmt = $this->db->prepare(
            "INSERT INTO directeuretudes (idUser, bureau)
             VALUES (?, ?)"
        );
        $stmt->execute([$idUser, $data['bureau']]);

        return $idUser;
    }

    // -------------------------------------------------------
    // Valider définitivement un mémoire
    // (après validation du professeur)
    // -------------------------------------------------------
    public function validerMemo(int $idMemoire): bool {
        // Vérifier que le mémoire est bien dans l'état 'valide'
        $stmt = $this->db->prepare(
            "SELECT * FROM memoire WHERE idMemoire = ? AND statut = 'valide'"
        );
        $stmt->execute([$idMemoire]);
        if (!$stmt->fetch()) return false;

        // 1. Mettre à jour la validation
        $stmt2 = $this->db->prepare(
            "UPDATE validation
             SET est_approuve_definitif = 1,
                 decision = 'approuve_definitif',
                 idDirecteur = ?
             WHERE idMemoire = ?
             ORDER BY date_decision DESC
             LIMIT 1"
        );
        $stmt2->execute([$_SESSION['idUser'], $idMemoire]);

        // 2. Mettre à jour le statut du mémoire
        $stmt3 = $this->db->prepare(
            "UPDATE memoire
             SET statut = 'approuve_definitif', idDirecteur = ?
             WHERE idMemoire = ?"
        );
        return $stmt3->execute([$_SESSION['idUser'], $idMemoire]);
    }

    // -------------------------------------------------------
    // Rejeter un mémoire (depuis le directeur)
    // -------------------------------------------------------
    public function rejeterMemo(int $idMemoire, string $commentaire = ''): bool {
        // 1. Insérer dans validation
        $stmt = $this->db->prepare(
            "INSERT INTO validation (decision, commentaire, idMemoire, idProfesseur, idDirecteur)
             SELECT 'rejete', ?, ?, idProfesseur, ?
             FROM validation
             WHERE idMemoire = ?
             ORDER BY date_decision DESC
             LIMIT 1"
        );
        $stmt->execute([$commentaire, $idMemoire, $_SESSION['idUser'], $idMemoire]);

        // 2. Mettre à jour le statut du mémoire
        $stmt2 = $this->db->prepare(
            "UPDATE memoire SET statut = 'rejete' WHERE idMemoire = ?"
        );
        return $stmt2->execute([$idMemoire]);
    }

    // -------------------------------------------------------
    // Archiver un mémoire approuvé définitivement
    // -------------------------------------------------------
    public function archiverMemo(int $idMemoire): bool {
        $stmt = $this->db->prepare(
            "UPDATE memoire
             SET statut = 'archive', est_archive = 1
             WHERE idMemoire = ? AND statut = 'approuve_definitif'"
        );
        return $stmt->execute([$idMemoire]);
    }

    // -------------------------------------------------------
    // Uploader un ancien mémoire (1 seul)
    // -------------------------------------------------------
    public function upload(array $data, array $fichier): int|false {
        // Vérifier format fichier
        $extensionsAutorisees = ['pdf', 'doc', 'docx'];
        $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $extensionsAutorisees)) return false;

        // Générer nom unique
        $nomFichier  = 'ancien_' . time() . '.' . $extension;
        $destination = __DIR__ . '/../../public/uploads/' . $nomFichier;

        if (!move_uploaded_file($fichier['tmp_name'], $destination)) return false;

        // Insérer en BDD directement avec statut approuve_definitif
        $stmt = $this->db->prepare(
            "INSERT INTO memoire
                (titre, theme, nbPages, centre, filiere, date_soumission,
                 annee_academique, statut, fichier, format_fichier,
                 idEtudiant, idProfesseur, idDirecteur)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'approuve_definitif', ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['titre'],
            $data['theme'],
            $data['nbPages']          ?? null,
            $data['centre']           ?? null,
            $data['filiere'],
            $data['date_soumission'],
            $data['annee_academique'],
            $nomFichier,
            strtoupper($extension) === 'PDF' ? 'PDF' : 'WORD',
            $data['idEtudiant'],
            $data['idProfesseur'],
            $_SESSION['idUser'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    // -------------------------------------------------------
    // Uploader plusieurs mémoires via fichier Excel
    // Le fichier Excel contient les infos, les PDF sont uploadés séparément
    // -------------------------------------------------------
    public function uploadExcel(array $memoires): array {
        $resultats = ['succes' => 0, 'erreurs' => []];

        foreach ($memoires as $index => $data) {
            try {
                $stmt = $this->db->prepare(
                    "INSERT INTO memoire
                        (titre, theme, filiere, annee_academique, statut,
                         fichier, format_fichier, idEtudiant, idProfesseur, idDirecteur,
                         date_soumission)
                     VALUES (?, ?, ?, ?, 'approuve_definitif', ?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([
                    $data['titre'],
                    $data['theme'],
                    $data['filiere'],
                    $data['annee_academique'],
                    $data['fichier']       ?? null,
                    $data['format_fichier'] ?? null,
                    $data['idEtudiant'],
                    $data['idProfesseur'],
                    $_SESSION['idUser'],
                    $data['date_soumission'] ?? date('Y-m-d'),
                ]);
                $resultats['succes']++;
            } catch (Exception $e) {
                $resultats['erreurs'][] = "Ligne {$index} : " . $e->getMessage();
            }
        }

        return $resultats;
    }

    // -------------------------------------------------------
    // Gérer les utilisateurs — liste complète
    // -------------------------------------------------------
    public function gererUsers(): array {
        return $this->getAllWithDetails();
    }

    // -------------------------------------------------------
    // Récupérer les mémoires en attente d'approbation
    // -------------------------------------------------------
    public function getMemoiresAApprouver(): array {
        $stmt = $this->db->query(
            "SELECT m.*,
                    u.name   AS nomEtudiant,
                    u.prenom AS prenomEtudiant,
                    ed.filiere, ed.matricule,
                    up.name   AS nomProfesseur,
                    up.prenom AS prenomProfesseur,
                    v.commentaire AS commentaireValidation,
                    v.date_decision
             FROM memoire m
             JOIN users u            ON m.idEtudiant   = u.idUser
             JOIN etudiantdiplome ed ON m.idEtudiant   = ed.idUser
             LEFT JOIN users up      ON m.idProfesseur = up.idUser
             LEFT JOIN validation v  ON v.idMemoire    = m.idMemoire
             WHERE m.statut = 'valide'
             ORDER BY v.date_decision ASC"
        );
        return $stmt->fetchAll();
    }

    // -------------------------------------------------------
    // Récupérer les commentaires signalés
    // -------------------------------------------------------
    public function getCommentairesSignales(): array {
        $stmt = $this->db->query(
            "SELECT c.*,
                    u.name   AS nomAuteur,
                    u.prenom AS prenomAuteur,
                    m.titre  AS titreMemoire
             FROM commentaire c
             JOIN users u   ON c.idUser    = u.idUser
             JOIN memoire m ON c.idMemoire = m.idMemoire
             WHERE c.estSignale = 1
             ORDER BY c.date_signalement DESC"
        );
        return $stmt->fetchAll();
    }

    // -------------------------------------------------------
    // Supprimer un commentaire signalé
    // -------------------------------------------------------
    public function supprimerCommentaire(int $idCommentaire): bool {
        $stmt = $this->db->prepare(
            "DELETE FROM commentaire WHERE idCommentaire = ?"
        );
        return $stmt->execute([$idCommentaire]);
    }

    // -------------------------------------------------------
    // Récupérer les infos complètes du directeur connecté
    // -------------------------------------------------------
    public function getMonProfil(): ?array {
        $stmt = $this->db->prepare(
            "SELECT u.*, de.bureau
             FROM users u
             JOIN directeuretudes de ON u.idUser = de.idUser
             WHERE u.idUser = ?"
        );
        $stmt->execute([$_SESSION['idUser']]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // -------------------------------------------------------
    // Assigner un jury à un mémoire
    // -------------------------------------------------------
    public function assignerJury(int $idMemoire, array $idProfesseurs): bool {
        // Supprimer l'ancien jury
        $stmt = $this->db->prepare(
            "DELETE FROM jury_memoire WHERE idMemoire = ?"
        );
        $stmt->execute([$idMemoire]);

        // Insérer le nouveau jury
        $stmt2 = $this->db->prepare(
            "INSERT INTO jury_memoire (idProfesseur, idMemoire) VALUES (?, ?)"
        );
        foreach ($idProfesseurs as $idProf) {
            $stmt2->execute([$idProf, $idMemoire]);
        }

        // Mettre à jour idProfesseur principal dans memoire
        $stmt3 = $this->db->prepare(
            "UPDATE memoire SET idProfesseur = ? WHERE idMemoire = ?"
        );
        return $stmt3->execute([$idProfesseurs[0], $idMemoire]);
    }
}