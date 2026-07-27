<?php

namespace App\Models;

use App\Core\Database;

/**
 * Configurações visuais do painel — sempre uma linha só (id=1). Usado pelo
 * header.php em toda página pra pintar logo/cores dinamicamente.
 */
class Configuracao
{
    private const PADRAO = [
        'id'                => 1,
        'nome_sistema'      => 'Enterprise Ranking',
        'logo'              => null,
        'favicon'           => null,
        'cor_primaria'      => '#f97316',
        'cor_barra_lateral' => '#212529',
    ];

    public static function obter(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('SELECT * FROM configuracoes WHERE id = 1');
        $config = $stmt->fetch();
        return $config ?: self::PADRAO;
    }

    public static function atualizar(array $dados): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            INSERT INTO configuracoes (id, nome_sistema, logo, favicon, cor_primaria, cor_barra_lateral, atualizado_por, atualizado_em)
            VALUES (1, :nome_sistema, :logo, :favicon, :cor_primaria, :cor_barra_lateral, :atualizado_por, NOW())
            ON DUPLICATE KEY UPDATE
                nome_sistema = VALUES(nome_sistema),
                logo = VALUES(logo),
                favicon = VALUES(favicon),
                cor_primaria = VALUES(cor_primaria),
                cor_barra_lateral = VALUES(cor_barra_lateral),
                atualizado_por = VALUES(atualizado_por),
                atualizado_em = VALUES(atualizado_em)
        ');
        $stmt->execute($dados);
    }
}