<?php

namespace App\Models;

use App\Core\Database;

class Cidade
{
    public static function listar(): array
    {
        $pdo = Database::getConnection();
        return $pdo->query('
            SELECT cidades.*, regionais.nome AS regional_nome
            FROM cidades
            LEFT JOIN regionais ON regionais.id = cidades.regional_id
            ORDER BY cidades.nome
        ')->fetchAll();
    }

    public static function buscarPorId(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM cidades WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function criar(string $nome, string $uf, ?int $regionalId): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO cidades (nome, uf, regional_id) VALUES (:nome, :uf, :regional_id)');
        $stmt->execute(['nome' => $nome, 'uf' => $uf, 'regional_id' => $regionalId]);
    }

    public static function atualizar(int $id, string $nome, string $uf, ?int $regionalId): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE cidades SET nome = :nome, uf = :uf, regional_id = :regional_id WHERE id = :id');
        $stmt->execute(['nome' => $nome, 'uf' => $uf, 'regional_id' => $regionalId, 'id' => $id]);
    }

    public static function excluir(int $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM cidades WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}