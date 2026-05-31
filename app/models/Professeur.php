<?php

require_once __DIR__ . '/User.php';

class Professeur extends User {

    // -------------------------------------------------------
    // Créer un compte Professeur complet
    // Insère dans users + professeur
    // -------------------------------------------------------
    public function creerCompteProfesseur(array $data): int {
        // 1. Insérer dans users
        $data['role'] = 'professeur';
        $idUser = $this->creerCompte($data);

        // 2. Insérer dans professeur
        $stmt = $this->db->prepare(
            "INSERT INTO professeur (idUser, specialite, grade, departement)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([
            $idUser,
            $data['specialite'],
            $data['grade'],
            $data['departement'],
        ]);

        return $idUser;
    }

    // -------------------------------------------------------
    // Récupérer les mémoires à évaluer
    // (mémoires dont le professeur est jury + statut en_attente)
    // -------------------------------------------------------
    public function getMemoiresAEvaluer(): array {
        $stmt = $this->db->prepare(
            "SELECT m.*,
                    u.name   AS nomEtudiant,
                    u.prenom AS prenomEtudiant,
                    ed.filiere, ed.matricule
             FROM memoire m
             JOIN jury_memoire jm     ON jm.idMemoire   = m.idMemoire
             JOIN users u             ON m.idEtudiant   = u.idUser
             JOIN etudiantdiplome ed  ON m.idEtudiant   = ed.idUser
             WHERE jm.idProfesseur = ?
             AND m.statut IN ('en_attente', 'modification_requise')
             ORDER BY m.date_soumission ASC"
        );
        $stmt->execute([$_SESSION['idUser']]);
        return $stmt->fetchAll();
    }

    // -------------------------------------------------------
    // Consulter tous les mémoires (lecture)
    // -------------------------------------------------------
    public function getMemoiresTous(): array {
        $stmt = $this->db->query(
            "SELECT m.*,
                    u.name   AS nomEtudiant,
                    u.prenom AS prenomEtudiant,
                    ed.filiere, ed.matricule
             FROM memoire m
             JOIN users u            ON m.idEtudiant = u.idUser
             JOIN etudiantdiplome ed ON m.idEtudiant = ed.idUser
             WHERE m.statut != 'archive'
             ORDER BY m.date_soumission DESC"
        );
        return $stmt->fetchAll();
    }

    // -------------------------------------------------------
    // Évaluer un mémoire (valider, rejeter ou demander modif)
    // -------------------------------------------------------
    public function evaluerMemo(int $idMemoire, string $decision, string $commentaire = ''): bool {
        // Vérifier que le professeur est bien jury de ce mémoire
        $stmt = $this->db->prepare(
            "SELECT * FROM jury_memoire
             WHERE idProfesseur = ? AND idMemoire = ?"
        );
        $stmt->execute([$_SESSION['idUser'], $idMemoire]);
        if (!$stmt->fetch()) return false;

        // Vérifier que la décision est valide
        $decisionsValides = ['valide', 'rejete', 'modification_requise'];
        if (!in_array($decision, $decisionsValides)) return false;

        // 1. Insérer dans validation
        $stmt2 = $this->db->prepare(
            "INSERT INTO validation (decision, commentaire, idMemoire, idProfesseur)
             VALUES (?, ?, ?, ?)"
        );
        $stmt2->execute([
            $decision,
            $commentaire,
            $idMemoire,
            $_SESSION['idUser'],
        ]);

        // 2. Mettre à jour le statut du mémoire
        $stmt3 = $this->db->prepare(
            "UPDATE memoire SET statut = ? WHERE idMemoire = ?"
        );
        return $stmt3->execute([$decision, $idMemoire]);
    }

    // -------------------------------------------------------
    // Ajouter une observation sur une validation
    // (appelé après evaluerMemo avec decision = modification_requise)
    // -------------------------------------------------------
    public function ajouterObs(int $idMemoire, string $contenu): bool {
        // Récupérer la dernière validation de ce mémoire par ce professeur
        $stmt = $this->db->prepare(
            "SELECT idValidation FROM validation
             WHERE idMemoire = ? AND idProfesseur = ?
             ORDER BY date_decision DESC
             LIMIT 1"
        );
        $stmt->execute([$idMemoire, $_SESSION['idUser']]);
        $validation = $stmt->fetch();

        if (!$validation) return false;

        // Insérer l'observation
        $stmt2 = $this->db->prepare(
            "INSERT INTO observation (contenu, idValidation)
             VALUES (?, ?)"
        );
        return $stmt2->execute([$contenu, $validation['idValidation']]);
    }

    // -------------------------------------------------------
    // Récupérer les observations d'une validation
    // -------------------------------------------------------
    public function getObservations(int $idMemoire): array {
        $stmt = $this->db->prepare(
            "SELECT o.*
             FROM observation o
             JOIN validation v ON o.idValidation = v.idValidation
             WHERE v.idMemoire = ? AND v.idProfesseur = ?
             ORDER BY o.date_observation ASC"
        );
        $stmt->execute([$idMemoire, $_SESSION['idUser']]);
        return $stmt->fetchAll();
    }

    // -------------------------------------------------------
    // Récupérer les infos complètes du professeur connecté
    // -------------------------------------------------------
    public function getMonProfil(): ?array {
        $stmt = $this->db->prepare(
            "SELECT u.*, p.specialite, p.grade, p.departement
             FROM users u
             JOIN professeur p ON u.idUser = p.idUser
             WHERE u.idUser = ?"
        );
        $stmt->execute([$_SESSION['idUser']]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // -------------------------------------------------------
    // Récupérer la liste de tous les professeurs
    // -------------------------------------------------------
    public static function getTous(PDO $db): array {
        $stmt = $db->query(
            "SELECT u.idUser, u.name, u.prenom,
                    p.specialite, p.grade, p.departement
             FROM users u
             JOIN professeur p ON u.idUser = p.idUser
             ORDER BY u.name ASC"
        );
        return $stmt->fetchAll();
    }
}