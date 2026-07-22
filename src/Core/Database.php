<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $host    = $_ENV['DB_HOST'];
            $db      = $_ENV['DB_NAME'];
            $user    = $_ENV['DB_USER'];
            $pass    = $_ENV['DB_PASS'];
            $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

            $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                die('Erro de conexão com o banco: ' . $e->getMessage());
            }
        }

        return self::$instance;
    }
}