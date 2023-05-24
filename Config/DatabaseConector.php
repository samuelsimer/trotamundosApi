<?php

class DatabaseConnector
{
    private static $instance = null;
    private $pdo;

    private function __construct($host, $dbname, $user, $pass)
    {
        $dsn = 'mysql:host=' . $host . ';dbname=' . $dbname;
        $this->pdo = new PDO($dsn, $user, $pass);
    }

    public static function getInstance($host, $dbname, $user, $pass)
    {
        if (self::$instance === null) {
            self::$instance = new DatabaseConnector($host, $dbname, $user, $pass);
        }
        return self::$instance;
    }

    public function query($query, $params = [])
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function escapeValue($value)
    {
        return $this->pdo->quote($value);
    }
}
