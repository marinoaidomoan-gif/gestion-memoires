<?php

require_once __DIR__ . '/User.php';

class Professeur extends User {

    // -------------------------------------------------------
    // Créer un compte Professeur
    // Insère dans users + professeur
    // -------------------------------------------------------
    public function creerCompteProfesseur(array $data): int {
        $data['role'] = 'professeur';
        $idUser = $this->creerCompte($data);

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
    // Mémoires encadrés par ce professeur
    // -------------------------------------------------------
    public function getMesMemoires(): array {
        $stmt = $this->db->prepare(
            "SELECT m.*, u.name AS nom_etudiant
             FROM memoire m
             LEFT JOIN users u ON m.idEtudiant = u.idUser
             WHERE m.idProfesseur = ?
             ORDER BY m.date_soumission DESC"
        );
        $stmt->execute([$_SESSION['idUser']]);
        return $stmt->fetchAll();
    }

    // -------------------------------------------------------
    // Évaluer un mémoire : valider ou rejeter
    // (simplifié : on met à jour directement le statut)
    // -------------------------------------------------------
    public function evaluerMemo(int $idMemoire, string $decision): bool {
        $decisionsValides = ['valide', 'rejete'];
        if (!in_array($decision, $decisionsValides)) return false;

        // Vérifier que le mémoire est bien encadré par ce professeur
        $stmt = $this->db->prepare(
            "SELECT idMemoire FROM memoire
             WHERE idMemoire = ? AND idProfesseur = ? AND statut = 'en_attente'"
        );
        $stmt->execute([$idMemoire, $_SESSION['idUser']]);
        if (!$stmt->fetch()) return false;

        $stmt2 = $this->db->prepare(
            "UPDATE memoire SET statut = ? WHERE idMemoire = ?"
        );
        return $stmt2->execute([$decision, $idMemoire]);
    }

    // -------------------------------------------------------
    // Ajouter une observation (commentaire interne) sur un mémoire
    // Utilise la table commentaire avec un flag interne
    // -------------------------------------------------------
    public function ajouterObs(int $idMemoire, string $contenu): int {
        $stmt = $this->db->prepare(
            "INSERT INTO commentaire (contenu, idUser, idMemoire)
             VALUES (?, ?, ?)"
        );
        $stmt->execute([$contenu, $_SESSION['idUser'], $idMemoire]);
        return (int) $this->db->lastInsertId();
    }

    // -------------------------------------------------------
    // Profil complet du professeur connecté
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
    // Liste de tous les professeurs (statique, utile au directeur)
    // -------------------------------------------------------
    public static function getTous(PDO $db): array {
        $stmt = $db->query(
            "SELECT u.idUser, u.name, p.specialite, p.grade, p.departement
             FROM users u
             JOIN professeur p ON u.idUser = p.idUser
             ORDER BY u.name ASC"
        );
        return $stmt->fetchAll();
    }
}