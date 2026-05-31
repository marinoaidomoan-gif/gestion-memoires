<?php

require_once __DIR__ . '/User.php';

class EtudiantDiplome extends User {

    // -------------------------------------------------------
    // Créer un compte EtudiantDiplome complet
    // Insère dans users + etudiantdiplome
    // -------------------------------------------------------
    public function creerCompteEtudiant(array $data): int {
        // 1. Insérer dans users
        $data['role'] = 'etudiant_diplome';
        $idUser = $this->creerCompte($data);

        // 2. Insérer dans etudiantdiplome
        $stmt = $this->db->prepare(
            "INSERT INTO etudiantdiplome (idUser, matricule, niveau, filiere, annee_diplome)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $idUser,
            $data['matricule'],
            $data['niveau'],
            $data['filiere'],
            $data['annee_diplome'] ?? null,
        ]);

        return $idUser;
    }

    // -------------------------------------------------------
    // Soumettre un mémoire
    // -------------------------------------------------------
    public function soumettreMemoire(array $data, array $fichier): int|false {
        // 1. Vérifier le format du fichier (PDF ou Word uniquement)
        $extensionsAutorisees = ['pdf', 'doc', 'docx'];
        $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $extensionsAutorisees)) {
            return false;
        }

        // 2. Générer un nom unique pour le fichier
        $nomFichier = $_SESSION['idUser'] . '_' . time() . '.' . $extension;
        $destination = __DIR__ . '/../../public/uploads/' . $nomFichier;

        // 3. Déplacer le fichier uploadé
        if (!move_uploaded_file($fichier['tmp_name'], $destination)) {
            return false;
        }

        // 4. Insérer le mémoire en BDD
        $stmt = $this->db->prepare(
            "INSERT INTO memoire
                (titre, theme, nbPages, centre, filiere, date_soumission,
                 annee_academique, statut, fichier, format_fichier, idEtudiant)
             VALUES (?, ?, ?, ?, ?, CURDATE(), ?, 'en_attente', ?, ?, ?)"
        );
        $stmt->execute([
            $data['titre'],
            $data['theme'],
            $data['nbPages']          ?? null,
            $data['centre']           ?? null,
            $data['filiere'],
            $data['annee_academique'],
            $nomFichier,
            strtoupper($extension) === 'PDF' ? 'PDF' : 'WORD',
            $_SESSION['idUser'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    // -------------------------------------------------------
    // Récupérer les mémoires de l'étudiant connecté
    // -------------------------------------------------------
    public function getMesMemoires(): array {
        $stmt = $this->db->prepare(
            "SELECT m.*,
                    p.idUser AS idProf,
                    u.name AS nomProfesseur,
                    u.prenom AS prenomProfesseur
             FROM memoire m
             LEFT JOIN users u ON m.idProfesseur = u.idUser
             LEFT JOIN professeur p ON m.idProfesseur = p.idUser
             WHERE m.idEtudiant = ?
             ORDER BY m.date_soumission DESC"
        );
        $stmt->execute([$_SESSION['idUser']]);
        return $stmt->fetchAll();
    }

    // -------------------------------------------------------
    // Récupérer l'état de validation d'un mémoire
    // -------------------------------------------------------
    public function getEtatValidation(int $idMemoire): ?array {
        $stmt = $this->db->prepare(
            "SELECT m.statut, m.titre,
                    v.decision, v.commentaire, v.date_decision,
                    u.name AS nomProfesseur, u.prenom AS prenomProfesseur,
                    GROUP_CONCAT(o.contenu SEPARATOR '||') AS observations
             FROM memoire m
             LEFT JOIN validation v  ON v.idMemoire = m.idMemoire
             LEFT JOIN users u       ON v.idProfesseur = u.idUser
             LEFT JOIN observation o ON o.idValidation = v.idValidation
             WHERE m.idMemoire = ? AND m.idEtudiant = ?
             GROUP BY m.idMemoire, v.idValidation"
        );
        $stmt->execute([$idMemoire, $_SESSION['idUser']]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // -------------------------------------------------------
    // Modifier les infos d'un mémoire
    // (seulement si statut = en_attente ou modification_requise)
    // -------------------------------------------------------
    public function modifierMemoire(int $idMemoire, array $data, ?array $fichier = null): bool {
        // Vérifier que le mémoire appartient à l'étudiant
        // et que le statut autorise la modification
        $stmt = $this->db->prepare(
            "SELECT * FROM memoire
             WHERE idMemoire = ? AND idEtudiant = ?
             AND statut IN ('en_attente', 'modification_requise')"
        );
        $stmt->execute([$idMemoire, $_SESSION['idUser']]);
        $memoire = $stmt->fetch();

        if (!$memoire) return false;

        // Si nouveau fichier fourni
        $nomFichier = $memoire['fichier'];
        if ($fichier && $fichier['error'] === 0) {
            $extensionsAutorisees = ['pdf', 'doc', 'docx'];
            $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));

            if (!in_array($extension, $extensionsAutorisees)) return false;

            $nomFichier = $_SESSION['idUser'] . '_' . time() . '.' . $extension;
            $destination = __DIR__ . '/../../public/uploads/' . $nomFichier;
            if (!move_uploaded_file($fichier['tmp_name'], $destination)) return false;

            $data['format_fichier'] = strtoupper($extension) === 'PDF' ? 'PDF' : 'WORD';
        }

        // Mettre à jour le mémoire
        $stmt2 = $this->db->prepare(
            "UPDATE memoire
             SET titre = ?, theme = ?, nbPages = ?,
                 centre = ?, filiere = ?, annee_academique = ?, fichier = ?
             WHERE idMemoire = ?"
        );
        return $stmt2->execute([
            $data['titre'],
            $data['theme'],
            $data['nbPages']          ?? null,
            $data['centre']           ?? null,
            $data['filiere'],
            $data['annee_academique'],
            $nomFichier,
            $idMemoire,
        ]);
    }

    // -------------------------------------------------------
    // Récupérer les infos complètes de l'étudiant connecté
    // -------------------------------------------------------
    public function getMonProfil(): ?array {
        $stmt = $this->db->prepare(
            "SELECT u.*, ed.matricule, ed.niveau, ed.filiere, ed.annee_diplome
             FROM users u
             JOIN etudiantdiplome ed ON u.idUser = ed.idUser
             WHERE u.idUser = ?"
        );
        $stmt->execute([$_SESSION['idUser']]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}