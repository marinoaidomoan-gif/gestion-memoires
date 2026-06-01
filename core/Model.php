<?php

abstract class Model {
    protected PDO $pdo;
    protected string $table;       // Nom de la table — défini dans chaque Model enfant
    protected string $primaryKey = 'id'; // Clé primaire — à surcharger si besoin

    public function __construct() {
        $this->pdo = Database::getInstance()->getPdo();
    }

    // Trouve un enregistrement par sa clé primaire
    public function find(int $id): array|false {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Retourne tous les enregistrements
    public function all(): array {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    // Insère un enregistrement
    // $data = ['colonne' => 'valeur', ...]
    public function create(array $data): int {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $stmt = $this->pdo->prepare(
            "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)"
        );
        $stmt->execute(array_values($data));
        return (int) $this->pdo->lastInsertId();
    }

    // Met à jour un enregistrement
    public function update(int $id, array $data): bool {
        $set = implode(', ', array_map(fn($col) => "$col = ?", array_keys($data)));

        $stmt = $this->pdo->prepare(
            "UPDATE {$this->table} SET $set WHERE {$this->primaryKey} = ?"
        );
        return $stmt->execute([...array_values($data), $id]);
    }

    // Supprime un enregistrement
    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare(
            "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?"
        );
        return $stmt->execute([$id]);
    }

    // Recherche avec conditions personnalisées
    // Exemple : $this->where('statut = ? AND filiere = ?', ['valide', 'Informatique'])
    public function where(string $condition, array $params = []): array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$this->table} WHERE $condition"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Compte les enregistrements
    public function count(string $condition = '1', array $params = []): int {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE $condition"
        );
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
}