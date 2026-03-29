<?php
/**
 * Configuration de la base de données SQLite
 * Petites Annonces - Projet PHP/SQLite
 */

class Database {
    private static $instance = null;
    private $connection;
    private $dbPath;

    private function __construct() {
        $this->dbPath = __DIR__ . '/../database/annonces.db';
        
        // Créer le dossier database s'il n'existe pas
        $dbDir = dirname($this->dbPath);
        if (!file_exists($dbDir)) {
            mkdir($dbDir, 0777, true);
        }

        try {
            $this->connection = new PDO('sqlite:' . $this->dbPath);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Activer les clés étrangères
            $this->connection->exec('PRAGMA foreign_keys = ON');
            
        } catch (PDOException $e) {
            die(json_encode([
                'success' => false,
                'message' => 'Erreur de connexion à la base de données: ' . $e->getMessage()
            ]));
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    // Empêcher le clonage
    private function __clone() {}

    // Empêcher la désérialisation
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
