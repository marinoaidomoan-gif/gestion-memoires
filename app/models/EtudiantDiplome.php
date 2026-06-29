<?php

require_once __DIR__ . '/User.php';

class EtudiantDiplome extends User {

    // -------------------------------------------------------
    // Créer un compte EtudiantDiplome
    // Insère dans users + etudiantdiplome
    // -------------------------------------------------------
    public function creerCompteEtudiant(array $data): int {
        $data['role'] = 'etudiant_diplome';
        $idUser = $this->creerCompte($data);

        $stmt = $this->db->prepare(
            "INSERT INTO etudiantdiplome (idUser, niveau, filiere, annee_diplome)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([
            $idUser,
            $data['niveau'],
            $data['filiere'],
            $data['annee_diplome'] ?? null,
        ]);

        return $idUser;
    }

    // -------------------------------------------------------
    // Soumettre un mémoire avec upload de fichier
    // -------------------------------------------------------
    public function soumettreMemoire(array $data, array $fichier): int|false {
        $extensionsAutorisees = ['pdf'];
        $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $extensionsAutorisees)) {
            return false;
        }

        // Récupération globale de la session utilisateur
        $idConnecte = $_SESSION['user']['id'] ?? null;
        $nomFichier  = $idConnecte . '_' . time() . '.' . $extension;
        $destination = __DIR__ . '/../../public/uploads/' . $nomFichier;

        if (!move_uploaded_file($fichier['tmp_name'], $destination)) {
            return false;
        }

        // Requête SQL alignée sur ta structure phpMyAdmin
        $stmt = $this->db->prepare(
            "INSERT INTO memoire
                (titre, theme, nbPages, centre, date_soumission,
                annee_academique, statut, fichier, idEtudiant, idProfesseur)
            VALUES (?, ?, ?, ?, CURDATE(), ?, 'en_attente', ?, ?, ?)"
        );
        
        $stmt->execute([
            $data['titre'],
            $data['theme'],
            $data['nbPages']         ?? null,
            $data['centre']          ?? null,
            $data['annee_academique'],
            $nomFichier,
            $idConnecte,
            $data['idProfesseur']    ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    // -------------------------------------------------------
    // Récupérer les mémoires de l'étudiant connecté
    // -------------------------------------------------------
    public function getMesMemoires(): array {
        $idConnecte = $_SESSION['user']['id'] ?? null;
        $stmt = $this->db->prepare(
            "SELECT m.*, u.name AS nom_professeur
             FROM memoire m
             LEFT JOIN users u ON m.idProfesseur = u.id_user
             WHERE m.idEtudiant = ?
             ORDER BY m.date_soumission DESC"
        );
        $stmt->execute([$idConnecte]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // -------------------------------------------------------
    // Modifier un mémoire (seulement si en_attente)
    // -------------------------------------------------------
    public function modifierMemoire(int $idMemoire, array $data, ?array $fichier = null): bool {
        $idConnecte = $_SESSION['user']['id'] ?? null;
        
        $stmt = $this->db->prepare(
            "SELECT * FROM memoire
             WHERE idMemoire = ? AND idEtudiant = ? AND statut = 'en_attente'"
        );
        $stmt->execute([$idMemoire, $idConnecte]);
        $memoire = $stmt->fetch();

        if (!$memoire) return false;

        $nomFichier = $memoire['fichier'];

        if ($fichier && $fichier['error'] === 0) {
            $extensionsAutorisees = ['pdf'];
            $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
            if (!in_array($extensionsAutorisees, $extension)) return false;

            $nomFichier  = $idConnecte . '_' . time() . '.' . $extension;
            $destination = __DIR__ . '/../../public/uploads/' . $nomFichier;
            if (!move_uploaded_file($fichier['tmp_name'], $destination)) return false;
        }

        $stmt2 = $this->db->prepare(
            "UPDATE memoire
             SET titre = ?, theme = ?, nbPages = ?, centre = ?,
                 annee_academique = ?, fichier = ?
             WHERE idMemoire = ?"
        );
        return $stmt2->execute([
            $data['titre'],
            $data['theme'],
            $data['nbPages']         ?? null,
            $data['centre']          ?? null,
            $data['annee_academique'],
            $nomFichier,
            $idMemoire,
        ]);
    }

    // -------------------------------------------------------
    // Profil complet de l'étudiant connecté
    // -------------------------------------------------------
    public function getMonProfil(): ?array {
        $stmt = $this->db->prepare(
            "SELECT u.*, ed.niveau, ed.filiere, ed.annee_diplome
            FROM users u
            JOIN etudiantdiplome ed ON u.id_user = ed.idUser
            WHERE u.id_user = ?"
        );
        $stmt->execute([$_SESSION['user']['id'] ?? null]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result : null;
    }
}