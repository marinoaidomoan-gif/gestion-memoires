<?php

class Database {
    private static ?Database $instance = null;
    private PDO $pdo;
    private string $host     = 'sql107.infinityfree.com';
    private string $dbname   = 'if0_42078155_gestion_memoire';
    private string $user     = 'if0_42078155';
    private string $password = '4BSc6ZzNdobh';
    private string $charset  = 'utf8mb4';

    private function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->pdo = new PDO($dsn, $this->user, $this->password, $options);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Connexion échouée : ' . $e->getMessage()]));
        }
    }

    // Empêche le clonage
    private function __clone() {}

    // Point d'accès unique
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getPdo(): PDO {
        return $this->pdo;
    }
}