<?php

namespace App\Models;

use App\Core\Database;

class Ranking
{
    /**
     * Cria um "ranking" (o mês/ano de referência), se ainda não existir, e retorna o id.
     */
    public static function localizarOuCriar(int $mes, int $ano, int $usuarioId): int
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare('SELECT id FROM rankings WHERE mes = :mes AND ano = :ano');
        $stmt->execute(['mes' => $mes, 'ano' => $ano]);
        $existente = $stmt->fetch();
        if ($existente) {
            return (int) $existente['id'];
        }

        $stmt = $pdo->prepare('INSERT INTO rankings (mes, ano, usuario_id, criado_em) VALUES (:mes, :ano, :usuario_id, NOW())');
        $stmt->execute(['mes' => $mes, 'ano' => $ano, 'usuario_id' => $usuarioId]);
        return (int) $pdo->lastInsertId();
    }

    public static function listar(): array
    {
        $pdo = Database::getConnection();
        return $pdo->query('
            SELECT rankings.*, usuarios.nome AS usuario_nome,
                   (SELECT COUNT(*) FROM artes_geradas WHERE artes_geradas.ranking_id = rankings.id) AS total_artes
            FROM rankings
            LEFT JOIN usuarios ON usuarios.id = rankings.usuario_id
            ORDER BY rankings.ano DESC, rankings.mes DESC
        ')->fetchAll();
    }

    public static function buscarPorId(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM rankings WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function excluir(int $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM rankings WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /**
     * Marca o ranking do mês como enviado ao time (WhatsApp, Instagram etc).
     * Puramente informativo pro R.H. saber o que já foi disparado.
     */
    public static function marcarComoEnviado(int $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE rankings SET enviado_em = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function desmarcarEnviado(int $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE rankings SET enviado_em = NULL WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}