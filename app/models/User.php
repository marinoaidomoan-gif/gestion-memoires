<?php

require_once __DIR__ . '/../../core/Model.php';

class User extends Model {

    protected string $table = 'users';

    // -------------------------------------------------------
    // Connexion
    // -------------------------------------------------------
    public function seConnecter(string $email, string $password): bool {
        $stmt = $this->db->prepare(
            "SELECT * FROM users WHERE email = ?"
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['idUser']  = $user['idUser'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['prenom']  = $user['prenom'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];
            return true;
        }
        return false;
    }

    // -------------------------------------------------------
    // Déconnexion
    // -------------------------------------------------------
    public function seDeconnecter(): void {
        session_destroy();
    }

    // -------------------------------------------------------
    // Trouver un utilisateur par email
    // -------------------------------------------------------
    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM users WHERE email = ?"
        );
        $stmt->execute([$email]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // -------------------------------------------------------
    // Trouver un utilisateur par id avec ses infos complètes
    // (jointure avec la table de spécialisation selon le rôle)
    // -------------------------------------------------------
    public function findByIdWithRole(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM users WHERE idUser = ?"
        );
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if (!$user) return null;

        // Jointure avec la table de spécialisation selon le rôle
        switch ($user['role']) {
            case 'etudiant_diplome':
                $stmt2 = $this->db->prepare(
                    "SELECT * FROM etudiantdiplome WHERE idUser = ?"
                );
                break;
            case 'etudiant_consulteur':
                $stmt2 = $this->db->prepare(
                    "SELECT * FROM etudiantconsulteur WHERE idUser = ?"
                );
                break;
            case 'professeur':
                $stmt2 = $this->db->prepare(
                    "SELECT * FROM professeur WHERE idUser = ?"
                );
                break;
            case 'directeur_etudes':
                $stmt2 = $this->db->prepare(
                    "SELECT * FROM directeuretudes WHERE idUser = ?"
                );
                break;
            default:
                return $user;
        }

        $stmt2->execute([$id]);
        $details = $stmt2->fetch();

        // Fusionner les deux tableaux
        return $details ? array_merge($user, $details) : $user;
    }

    // -------------------------------------------------------
    // Commenter un mémoire
    // -------------------------------------------------------
    public function commenter(int $idMemoire, string $contenu): int {
        $stmt = $this->db->prepare(
            "INSERT INTO commentaire (contenu, idUser, idMemoire)
             VALUES (?, ?, ?)"
        );
        $stmt->execute([$contenu, $_SESSION['idUser'], $idMemoire]);
        return (int) $this->db->lastInsertId();
    }

    // -------------------------------------------------------
    // Liker un mémoire
    // -------------------------------------------------------
    public function liker(int $idMemoire): bool {
        // Vérifier si déjà liké
        $stmt = $this->db->prepare(
            "SELECT idLike FROM likes
             WHERE idUser = ? AND idMemoire = ?"
        );
        $stmt->execute([$_SESSION['idUser'], $idMemoire]);

        if ($stmt->fetch()) {
            // Déjà liké → unlike
            $stmt2 = $this->db->prepare(
                "DELETE FROM likes WHERE idUser = ? AND idMemoire = ?"
            );
            $stmt2->execute([$_SESSION['idUser'], $idMemoire]);
            return false; // false = unliké
        }

        // Pas encore liké → like
        $stmt2 = $this->db->prepare(
            "INSERT INTO likes (idUser, idMemoire) VALUES (?, ?)"
        );
        $stmt2->execute([$_SESSION['idUser'], $idMemoire]);
        return true; // true = liké
    }

    // -------------------------------------------------------
    // Créer un compte utilisateur (par le DirecteurEtudes)
    // -------------------------------------------------------
    public function creerCompte(array $data): int {
        // Hasher le mot de passe
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        $stmt = $this->db->prepare(
            "INSERT INTO users (name, prenom, email, password, telephone, role)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['name'],
            $data['prenom'],
            $data['email'],
            $data['password'],
            $data['telephone'] ?? null,
            $data['role'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    // -------------------------------------------------------
    // Lister tous les utilisateurs avec leurs infos de rôle
    // -------------------------------------------------------
    public function getAllWithDetails(): array {
        $stmt = $this->db->query(
            "SELECT u.*,
                    ed.matricule, ed.niveau AS niveau_diplome,
                    ed.filiere AS filiere_diplome, ed.annee_diplome,
                    ec.niveau AS niveau_consulteur,
                    ec.filiere AS filiere_consulteur,
                    p.specialite, p.grade, p.departement,
                    de.bureau
             FROM users u
             LEFT JOIN etudiantdiplome    ed ON u.idUser = ed.idUser
             LEFT JOIN etudiantconsulteur ec ON u.idUser = ec.idUser
             LEFT JOIN professeur          p ON u.idUser = p.idUser
             LEFT JOIN directeuretudes    de ON u.idUser = de.idUser
             ORDER BY u.date_inscription DESC"
        );
        return $stmt->fetchAll();
    }

    // -------------------------------------------------------
    // Modifier un utilisateur
    // -------------------------------------------------------
    public function modifierUser(int $idUser, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE users
             SET name = ?, prenom = ?, email = ?, telephone = ?
             WHERE idUser = ?"
        );
        return $stmt->execute([
            $data['name'],
            $data['prenom'],
            $data['email'],
            $data['telephone'] ?? null,
            $idUser,
        ]);
    }

    // -------------------------------------------------------
    // Supprimer un utilisateur
    // -------------------------------------------------------
    public function supprimerUser(int $idUser): bool {
        $stmt = $this->db->prepare(
            "DELETE FROM users WHERE idUser = ?"
        );
        return $stmt->execute([$idUser]);
    }
}