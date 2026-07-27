<?php

namespace App\Models;

use App\Core\Database;

class ArteGerada
{
    public static function registrar(array $dados): int
    {
        // 'setor' é opcional (só faz sentido pro Ranking) — os outros templates
        // (boas-vindas, promoção, aniversário) não mandam essa chave.
        $dados = array_merge(['setor' => null], $dados);

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            INSERT INTO artes_geradas
                (ranking_id, colaborador_id, tipo, colocacao, setor, mes, ano, caminho_imagem, gerado_por)
            VALUES
                (:ranking_id, :colaborador_id, :tipo, :colocacao, :setor, :mes, :ano, :caminho_imagem, :gerado_por)
        ');
        $stmt->execute($dados);
        return (int) $pdo->lastInsertId();
    }

    public static function listarPorRanking(int $rankingId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT artes_geradas.*, colaboradores.nome AS colaborador_nome
            FROM artes_geradas
            LEFT JOIN colaboradores ON colaboradores.id = artes_geradas.colaborador_id
            WHERE artes_geradas.ranking_id = :ranking_id
            ORDER BY artes_geradas.colocacao ASC
        ');
        $stmt->execute(['ranking_id' => $rankingId]);
        return $stmt->fetchAll();
    }

    public static function buscarPorId(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM artes_geradas WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Igual listarPorRanking, mas já ordena por setor + colocação —
     * usado pra montar o ZIP em pastas por setor.
     */
    public static function listarPorRankingAgrupadoPorSetor(int $rankingId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT artes_geradas.*, colaboradores.nome AS colaborador_nome
            FROM artes_geradas
            LEFT JOIN colaboradores ON colaboradores.id = artes_geradas.colaborador_id
            WHERE artes_geradas.ranking_id = :ranking_id
            ORDER BY artes_geradas.setor ASC, artes_geradas.colocacao ASC
        ');
        $stmt->execute(['ranking_id' => $rankingId]);
        return $stmt->fetchAll();
    }

    public static function excluirPorRanking(int $rankingId): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM artes_geradas WHERE ranking_id = :ranking_id');
        $stmt->execute(['ranking_id' => $rankingId]);
    }

    public static function excluir(int $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM artes_geradas WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}