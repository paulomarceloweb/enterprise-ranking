<?php

namespace App\Models;

use App\Core\Database;

class Departamento
{
    public static function listar(): array
    {
        $pdo = Database::getConnection();
        return $pdo->query('SELECT * FROM departamentos ORDER BY nome')->fetchAll();
    }

    public static function buscarPorId(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM departamentos WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function criar(string $nome): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO departamentos (nome) VALUES (:nome)');
        $stmt->execute(['nome' => $nome]);
    }

    public static function atualizar(int $id, string $nome): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE departamentos SET nome = :nome WHERE id = :id');
        $stmt->execute(['nome' => $nome, 'id' => $id]);
    }

    public static function excluir(int $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM departamentos WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}