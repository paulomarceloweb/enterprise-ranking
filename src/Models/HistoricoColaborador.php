<?php

namespace App\Models;

use App\Core\Database;

class HistoricoColaborador
{
    public static function registrar(
        int $colaboradorId,
        string $tipo,
        string $dataEvento,
        ?string $detalhes = null,
        ?int $usuarioId = null,
        ?string $cargoAnterior = null,
        ?string $cargoNovo = null
    ): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            INSERT INTO colaboradores_historico
                (colaborador_id, tipo, data_evento, cargo_anterior, cargo_novo, detalhes, criado_por)
            VALUES
                (:colaborador_id, :tipo, :data_evento, :cargo_anterior, :cargo_novo, :detalhes, :criado_por)
        ');
        $stmt->execute([
            'colaborador_id' => $colaboradorId,
            'tipo' => $tipo,
            'data_evento' => $dataEvento,
            'cargo_anterior' => $cargoAnterior,
            'cargo_novo' => $cargoNovo,
            'detalhes' => $detalhes,
            'criado_por' => $usuarioId,
        ]);
    }

    public static function listarPorColaborador(int $colaboradorId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT * FROM colaboradores_historico
            WHERE colaborador_id = :id
            ORDER BY data_evento DESC, id DESC
        ');
        $stmt->execute(['id' => $colaboradorId]);
        return $stmt->fetchAll();
    }
}