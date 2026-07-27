<?php

namespace App\Models;

use App\Core\Database;

class Ocorrencia
{
    public static function registrar(array $dados): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            INSERT INTO ocorrencias
                (colaborador_id, tipo_ocorrencia_id, data_evento, descricao, arquivo, nome_arquivo_original, criado_por)
            VALUES
                (:colaborador_id, :tipo_ocorrencia_id, :data_evento, :descricao, :arquivo, :nome_arquivo_original, :criado_por)
        ');
        $stmt->execute($dados);
        return (int) $pdo->lastInsertId();
    }

    public static function listarPorColaborador(int $colaboradorId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT ocorrencias.*, tipos_ocorrencia.nome AS tipo_nome, usuarios.nome AS usuario_nome
            FROM ocorrencias
            LEFT JOIN tipos_ocorrencia ON tipos_ocorrencia.id = ocorrencias.tipo_ocorrencia_id
            LEFT JOIN usuarios ON usuarios.id = ocorrencias.criado_por
            WHERE ocorrencias.colaborador_id = :colaborador_id
            ORDER BY ocorrencias.data_evento DESC, ocorrencias.id DESC
        ');
        $stmt->execute(['colaborador_id' => $colaboradorId]);
        return $stmt->fetchAll();
    }

    public static function buscarPorId(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT ocorrencias.*, tipos_ocorrencia.nome AS tipo_nome
            FROM ocorrencias
            LEFT JOIN tipos_ocorrencia ON tipos_ocorrencia.id = ocorrencias.tipo_ocorrencia_id
            WHERE ocorrencias.id = :id
        ');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function excluir(int $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM ocorrencias WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}