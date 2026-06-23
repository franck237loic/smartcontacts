<?php

namespace App\Models;

use PDO;
use PDOException;

/**
 * Database Connection Class
 * Singleton pattern for database connection
 */
class Database
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        $config = require __DIR__ . '/../Config/config.php';
        
        try {
            $dsn = "mysql:host={$config['database']['host']};dbname={$config['database']['dbname']};charset={$config['database']['charset']}";
            $this->connection = new PDO(
                $dsn,
                $config['database']['username'],
                $config['database']['password'],
                $config['database']['options']
            );
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('Database query error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function fetchAll($sql, $params = [])
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetchOne($sql, $params = [])
    {
        return $this->query($sql, $params)->fetch();
    }

    public function execute($sql, $params = [])
    {
        return $this->query($sql, $params)->rowCount();
    }

    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    // Prevent cloning
    private function __clone() {}

    // Prevent unserialization
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
