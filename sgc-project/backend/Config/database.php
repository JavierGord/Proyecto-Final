<?php
declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $host     = 'localhost';
            $dbName   = 'sgc_comercial';
            $username = 'root';
            $password = '';
            $charset  = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$dbName;charset=$charset";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, $username, $password, $options);
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
?>