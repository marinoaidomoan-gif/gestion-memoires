<?php

abstract class Model {

    // Connexion PDO partagée par tous les models
    protected PDO $db;

    // Nom de la table (défini dans chaque model enfant)
    protected string $table;

    // -------------------------------------------------------
    // Constructeur — récupère la connexion
    // -------------------------------------------------------
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // -------------------------------------------------------
    // Trouver un enregistrement par son id
    // -------------------------------------------------------
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE id{$this->table} = ?"
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // -------------------------------------------------------
    // Récupérer tous les enregistrements
    // -------------------------------------------------------
    public function findAll(): array {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    // -------------------------------------------------------
    // Insérer un enregistrement
    // Paramètre : tableau associatif [colonne => valeur]
    // -------------------------------------------------------
    public function insert(array $data): int {
        $colonnes    = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} ({$colonnes}) VALUES ({$placeholders})"
        );
        $stmt->execute(array_values($data));

        return (int) $this->db->lastInsertId();
    }

    // -------------------------------------------------------
    // Mettre à jour un enregistrement par son id
    // -------------------------------------------------------
    public function update(int $id, array $data): bool {
        $set = implode(', ', array_map(
            fn($col) => "{$col} = ?",
            array_keys($data)
        ));

        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET {$set} WHERE id{$this->table} = ?"
        );
        return $stmt->execute([...array_values($data), $id]);
    }

    // -------------------------------------------------------
    // Supprimer un enregistrement par son id
    // -------------------------------------------------------
    public function delete(int $id): bool {
        $stmt = $this->db->prepare(
            "DELETE FROM {$this->table} WHERE id{$this->table} = ?"
        );
        return $stmt->execute([$id]);
    }

    // -------------------------------------------------------
    // Trouver avec conditions personnalisées
    // Exemple : findWhere(['statut' => 'en_attente'])
    // -------------------------------------------------------
    public function findWhere(array $conditions): array {
        $where = implode(' AND ', array_map(
            fn($col) => "{$col} = ?",
            array_keys($conditions)
        ));

        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE {$where}"
        );
        $stmt->execute(array_values($conditions));
        return $stmt->fetchAll();
    }

    // -------------------------------------------------------
    // Compter les enregistrements
    // -------------------------------------------------------
    public function count(array $conditions = []): int {
        if (empty($conditions)) {
            $stmt = $this->db->query(
                "SELECT COUNT(*) FROM {$this->table}"
            );
        } else {
            $where = implode(' AND ', array_map(
                fn($col) => "{$col} = ?",
                array_keys($conditions)
            ));
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM {$this->table} WHERE {$where}"
            );
            $stmt->execute(array_values($conditions));
        }
        return (int) $stmt->fetchColumn();
    }
}