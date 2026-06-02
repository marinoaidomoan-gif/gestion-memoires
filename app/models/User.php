<?php

require_once __DIR__ . '/../../core/Model.php';

class User extends Model {

    protected string $table      = 'users';
    protected string $primaryKey = 'idUser';

    // Trouve un utilisateur par email
    public function findByEmail(string $email): array|false {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM users WHERE email = ? LIMIT 1"
        );
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    // Récupère les données spécifiques au rôle (sous-type)
    // Retourne aussi le nom du rôle détecté
    public function getRoleData(int $idUser): array {
        $tables = [
            'directeur'  => 'directeur_etudes',
            'professeur' => 'professeur',
            'etudiant'   => 'etudiant_diplome',
            'consulteur' => 'etudiant_consulteur',
        ];

        foreach ($tables as $role => $table) {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM $table WHERE idUser = ? LIMIT 1"
            );
            $stmt->execute([$idUser]);
            $data = $stmt->fetch();
            if ($data) {
                return ['role' => $role, 'data' => $data];
            }
        }

        return ['role' => 'inconnu', 'data' => []];
    }

    // Crée un utilisateur + son sous-type en une transaction
    // $userData   = ['name'=>..., 'email'=>..., 'password'=>...]
    // $role       = 'etudiant' | 'consulteur' | 'professeur' | 'directeur'
    // $roleData   = données spécifiques au rôle
    public function creerAvecRole(array $userData, string $role, array $roleData): int|false {
        try {
            $this->pdo->beginTransaction();

            // Hash du mot de passe
            $userData['password'] = password_hash($userData['password'], PASSWORD_BCRYPT);

            // Insertion dans users
            $idUser = $this->create($userData);

            // Table du sous-type
            $tables = [
                'directeur'  => 'directeur_etudes',
                'professeur' => 'professeur',
                'etudiant'   => 'etudiant_diplome',
                'consulteur' => 'etudiant_consulteur',
            ];

            if (!isset($tables[$role])) {
                throw new Exception("Rôle inconnu : $role");
            }

            $roleData['idUser'] = $idUser;
            $columns      = implode(', ', array_keys($roleData));
            $placeholders = implode(', ', array_fill(0, count($roleData), '?'));

            $stmt = $this->pdo->prepare(
                "INSERT INTO {$tables[$role]} ($columns) VALUES ($placeholders)"
            );
            $stmt->execute(array_values($roleData));

            $this->pdo->commit();
            return $idUser;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
}