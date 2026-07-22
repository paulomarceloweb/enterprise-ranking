<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Core\Database;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

try {
    $pdo = Database::getConnection();
    echo "<h2>Conexão com o banco de dados bem-sucedida!</h2>";

    $stmt = $pdo->query("SHOW TABLES");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Tabelas encontradas (" . count($tabelas) . "):</p><ul>";
    foreach ($tabelas as $tabela) {
        echo "<li>" . htmlspecialchars($tabela) . "</li>";
    }
    echo "</ul>";
} catch (\Throwable $e) {
    echo "<h2>Erro:</h2><p>" . htmlspecialchars($e->getMessage()) . "</p>";
}