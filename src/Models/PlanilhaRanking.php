<?php

namespace App\Models;

use App\Core\Database;

class PlanilhaRanking
{
    public static function registrar(array $dados): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            INSERT INTO planilhas_ranking
                (ranking_id, nome_original, caminho_arquivo, aba_processada, mes, ano, total_entradas, total_gerados, usuario_id)
            VALUES
                (:ranking_id, :nome_original, :caminho_arquivo, :aba_processada, :mes, :ano, :total_entradas, :total_gerados, :usuario_id)
        ');
        $stmt->execute($dados);
        return (int) $pdo->lastInsertId();
    }

    public static function listar(): array
    {
        $pdo = Database::getConnection();
        return $pdo->query('
            SELECT planilhas_ranking.*, usuarios.nome AS usuario_nome
            FROM planilhas_ranking
            LEFT JOIN usuarios ON usuarios.id = planilhas_ranking.usuario_id
            ORDER BY planilhas_ranking.criado_em DESC
        ')->fetchAll();
    }

    public static function buscarPorId(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM planilhas_ranking WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }
}