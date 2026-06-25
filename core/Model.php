<?php

abstract class Model {

    // Connexion PDO partagée par tous les models
    protected $db;

    // Nom de la table (défini dans chaque model enfant)
    protected $table;

    // -------------------------------------------------------
    // Constructeur — récupère la connexion
    // -------------------------------------------------------
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // -------------------------------------------------------
    // Trouver un enregistrement par son id
    // -------------------------------------------------------
    public function findById($id) {
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
    public function findAll() {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    // -------------------------------------------------------
    // Insérer un enregistrement
    // -------------------------------------------------------
    public function insert($data) {
        $colonnes     = implode(', ', array_keys($data));
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
    public function update($id, $data) {
        $keys = array_keys($data);
        $set  = implode(', ', array_map(function($col) {
            return "{$col} = ?";
        }, $keys));

        $values   = array_values($data);
        $values[] = $id;

        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET {$set} WHERE id{$this->table} = ?"
        );
        return $stmt->execute($values);
    }

    // -------------------------------------------------------
    // Supprimer un enregistrement par son id
    // -------------------------------------------------------
    public function delete($id) {
        $stmt = $this->db->prepare(
            "DELETE FROM {$this->table} WHERE id{$this->table} = ?"
        );
        return $stmt->execute([$id]);
    }

    // -------------------------------------------------------
    // Trouver avec conditions personnalisées
    // -------------------------------------------------------
    public function findWhere($conditions) {
        $keys  = array_keys($conditions);
        $where = implode(' AND ', array_map(function($col) {
            return "{$col} = ?";
        }, $keys));

        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE {$where}"
        );
        $stmt->execute(array_values($conditions));
        return $stmt->fetchAll();
    }

    // -------------------------------------------------------
    // Compter les enregistrements
    // -------------------------------------------------------
    public function count($conditions = []) {
        if (empty($conditions)) {
            $stmt = $this->db->query(
                "SELECT COUNT(*) FROM {$this->table}"
            );
        } else {
            $keys  = array_keys($conditions);
            $where = implode(' AND ', array_map(function($col) {
                return "{$col} = ?";
            }, $keys));

            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM {$this->table} WHERE {$where}"
            );
            $stmt->execute(array_values($conditions));
        }
        return (int) $stmt->fetchColumn();
    }
}