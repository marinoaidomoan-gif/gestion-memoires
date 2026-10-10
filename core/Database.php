<?php

class Database {

    // Instance unique (pattern Singleton)
    private static $instance = null;

    // Connexion PDO
    private $pdo;

    // -------------------------------------------------------
    // Constructeur privé — connexion à MySQL
    // -------------------------------------------------------
    private function __construct() {
        require_once __DIR__ . '/../config/database.php';

        $dsn = "mysql:host=" . DB_HOST
             . ";dbname="    . DB_NAME
             . ";charset="   . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // En production, ne pas afficher le message d'erreur
            die(json_encode([
                'success' => false,
                'message' => 'Erreur de connexion à la base de données.'
            ]));
        }
    }

    // -------------------------------------------------------
    // Récupérer l'instance unique
    // -------------------------------------------------------
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // -------------------------------------------------------
    // Récupérer la connexion PDO
    // -------------------------------------------------------
    public function getConnection(): PDO {
        return $this->pdo;
    }

    // -------------------------------------------------------
    // Empêcher la copie et la sérialisation
    // -------------------------------------------------------
    private function __clone() {}
    public function __wakeup() {}
}