<?php

namespace App\Models;

use App\Core\Database;

/**
 * Agrega dados de várias tabelas pros relatórios. Diferente dos outros
 * Models, este não representa uma tabela — só concentra as queries de
 * relatório num lugar só.
 */
class Relatorio
{
    /**
     * Contagens gerais pro relatório demográfico: sexo, estado civil,
     * filhos, faixa etária, por departamento e por cidade. Considera só
     * colaboradores ativos, pra não distorcer com quem já saiu.
     */
    public static function demografico(): array
    {
        $pdo = Database::getConnection();

        $porSexo = $pdo->query("
            SELECT sexo, COUNT(*) AS total FROM colaboradores WHERE status = 'ativo' GROUP BY sexo
        ")->fetchAll();

        $porEstadoCivil = $pdo->query("
            SELECT COALESCE(estado_civil, 'nao_informado') AS estado_civil, COUNT(*) AS total
            FROM colaboradores WHERE status = 'ativo' GROUP BY COALESCE(estado_civil, 'nao_informado')
        ")->fetchAll();

        $porFilhos = $pdo->query("
            SELECT
                CASE WHEN quantidade_filhos IS NULL OR quantidade_filhos = 0 THEN 'Sem filhos' ELSE 'Com filhos' END AS grupo,
                COUNT(*) AS total
            FROM colaboradores WHERE status = 'ativo'
            GROUP BY grupo
        ")->fetchAll();

        $porFaixaEtaria = $pdo->query("
            SELECT
                CASE
                    WHEN TIMESTAMPDIFF(YEAR, data_nascimento, CURDATE()) < 25 THEN 'Até 24 anos'
                    WHEN TIMESTAMPDIFF(YEAR, data_nascimento, CURDATE()) BETWEEN 25 AND 34 THEN '25 a 34 anos'
                    WHEN TIMESTAMPDIFF(YEAR, data_nascimento, CURDATE()) BETWEEN 35 AND 44 THEN '35 a 44 anos'
                    WHEN TIMESTAMPDIFF(YEAR, data_nascimento, CURDATE()) BETWEEN 45 AND 54 THEN '45 a 54 anos'
                    ELSE '55 anos ou mais'
                END AS faixa,
                COUNT(*) AS total
            FROM colaboradores
            WHERE status = 'ativo' AND data_nascimento IS NOT NULL
            GROUP BY faixa
        ")->fetchAll();

        $porDepartamento = $pdo->query("
            SELECT departamentos.nome AS departamento, COUNT(*) AS total
            FROM colaboradores
            LEFT JOIN departamentos ON departamentos.id = colaboradores.departamento_id
            WHERE colaboradores.status = 'ativo'
            GROUP BY departamentos.nome
            ORDER BY total DESC
        ")->fetchAll();

        $porCidade = $pdo->query("
            SELECT CONCAT(cidades.nome, '/', cidades.uf) AS cidade, COUNT(*) AS total
            FROM colaboradores
            LEFT JOIN cidades ON cidades.id = colaboradores.cidade_id
            WHERE colaboradores.status = 'ativo'
            GROUP BY cidades.nome, cidades.uf
            ORDER BY total DESC
        ")->fetchAll();

        $totais = $pdo->query("
            SELECT
                SUM(CASE WHEN status = 'ativo' THEN 1 ELSE 0 END) AS ativos,
                SUM(CASE WHEN status = 'desligado' THEN 1 ELSE 0 END) AS desligados
            FROM colaboradores
        ")->fetch();

        return [
            'por_sexo'          => $porSexo,
            'por_estado_civil'  => $porEstadoCivil,
            'por_filhos'        => $porFilhos,
            'por_faixa_etaria'  => $porFaixaEtaria,
            'por_departamento'  => $porDepartamento,
            'por_cidade'        => $porCidade,
            'total_ativos'      => (int) ($totais['ativos'] ?? 0),
            'total_desligados'  => (int) ($totais['desligados'] ?? 0),
        ];
    }

    /**
     * Aniversariantes dos próximos N dias (considera a virada de ano).
     * Só colaboradores ativos.
     */
    public static function aniversariantesFuturos(int $dias = 30): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT colaboradores.*, departamentos.nome AS departamento_nome,
                   DATE_ADD(
                       data_nascimento,
                       INTERVAL (
                           YEAR(CURDATE()) - YEAR(data_nascimento)
                           + IF(DAYOFYEAR(data_nascimento) < DAYOFYEAR(CURDATE()), 1, 0)
                       ) YEAR
                   ) AS proximo_aniversario
            FROM colaboradores
            LEFT JOIN departamentos ON departamentos.id = colaboradores.departamento_id
            WHERE colaboradores.status = 'ativo' AND data_nascimento IS NOT NULL
            HAVING proximo_aniversario BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :dias DAY)
            ORDER BY proximo_aniversario
        ");
        $stmt->execute(['dias' => $dias]);
        return $stmt->fetchAll();
    }

    /**
     * Histórico de promoções, com filtro opcional de período (data_evento).
     */
    public static function historicoPromocoes(?string $inicio = null, ?string $fim = null): array
    {
        $pdo = Database::getConnection();

        $condicoes = ["colaboradores_historico.tipo = 'promocao'"];
        $parametros = [];

        if (!empty($inicio)) {
            $condicoes[] = 'colaboradores_historico.data_evento >= :inicio';
            $parametros['inicio'] = $inicio;
        }
        if (!empty($fim)) {
            $condicoes[] = 'colaboradores_historico.data_evento <= :fim';
            $parametros['fim'] = $fim;
        }

        $sql = '
            SELECT colaboradores_historico.*, colaboradores.nome AS colaborador_nome
            FROM colaboradores_historico
            LEFT JOIN colaboradores ON colaboradores.id = colaboradores_historico.colaborador_id
            WHERE ' . implode(' AND ', $condicoes) . '
            ORDER BY colaboradores_historico.data_evento DESC
        ';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }

    /**
     * Histórico de ocorrências, com filtros opcionais de período e tipo.
     */
    public static function historicoOcorrencias(?string $inicio = null, ?string $fim = null, ?int $tipoOcorrenciaId = null): array
    {
        $pdo = Database::getConnection();

        $condicoes = [];
        $parametros = [];

        if (!empty($inicio)) {
            $condicoes[] = 'ocorrencias.data_evento >= :inicio';
            $parametros['inicio'] = $inicio;
        }
        if (!empty($fim)) {
            $condicoes[] = 'ocorrencias.data_evento <= :fim';
            $parametros['fim'] = $fim;
        }
        if (!empty($tipoOcorrenciaId)) {
            $condicoes[] = 'ocorrencias.tipo_ocorrencia_id = :tipo_ocorrencia_id';
            $parametros['tipo_ocorrencia_id'] = $tipoOcorrenciaId;
        }

        $sql = '
            SELECT ocorrencias.*, colaboradores.nome AS colaborador_nome, tipos_ocorrencia.nome AS tipo_nome
            FROM ocorrencias
            LEFT JOIN colaboradores ON colaboradores.id = ocorrencias.colaborador_id
            LEFT JOIN tipos_ocorrencia ON tipos_ocorrencia.id = ocorrencias.tipo_ocorrencia_id
        ';
        if (!empty($condicoes)) {
            $sql .= ' WHERE ' . implode(' AND ', $condicoes);
        }
        $sql .= ' ORDER BY ocorrencias.data_evento DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }

    /**
     * Turnover mês a mês num ano: quantas admissões e quantos desligamentos.
     * Devolve um array com as 12 posições (índice 1 a 12) já preenchidas.
     */
    public static function turnover(int $ano): array
    {
        $pdo = Database::getConnection();

        $stmtAdmissoes = $pdo->prepare('
            SELECT MONTH(data_admissao) AS mes, COUNT(*) AS total
            FROM colaboradores
            WHERE YEAR(data_admissao) = :ano
            GROUP BY MONTH(data_admissao)
        ');
        $stmtAdmissoes->execute(['ano' => $ano]);
        $admissoesPorMes = array_column($stmtAdmissoes->fetchAll(), 'total', 'mes');

        $stmtDesligamentos = $pdo->prepare('
            SELECT MONTH(data_desligamento) AS mes, COUNT(*) AS total
            FROM colaboradores
            WHERE YEAR(data_desligamento) = :ano
            GROUP BY MONTH(data_desligamento)
        ');
        $stmtDesligamentos->execute(['ano' => $ano]);
        $desligamentosPorMes = array_column($stmtDesligamentos->fetchAll(), 'total', 'mes');

        $resultado = [];
        for ($mes = 1; $mes <= 12; $mes++) {
            $resultado[$mes] = [
                'admissoes'     => (int) ($admissoesPorMes[$mes] ?? 0),
                'desligamentos' => (int) ($desligamentosPorMes[$mes] ?? 0),
            ];
        }
        return $resultado;
    }

    /**
     * Ranking de colocações num ano: quantas vezes cada colaborador ficou
     * em 1º, 2º ou 3º lugar nas artes de ranking geradas.
     */
    public static function rankingPeriodo(int $ano): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT
                colaboradores.nome AS colaborador_nome,
                SUM(CASE WHEN artes_geradas.colocacao = 1 THEN 1 ELSE 0 END) AS primeiros,
                SUM(CASE WHEN artes_geradas.colocacao = 2 THEN 1 ELSE 0 END) AS segundos,
                SUM(CASE WHEN artes_geradas.colocacao = 3 THEN 1 ELSE 0 END) AS terceiros,
                COUNT(*) AS total_aparicoes
            FROM artes_geradas
            LEFT JOIN colaboradores ON colaboradores.id = artes_geradas.colaborador_id
            WHERE artes_geradas.tipo = 'ranking' AND artes_geradas.ano = :ano
            GROUP BY artes_geradas.colaborador_id, colaboradores.nome
            ORDER BY primeiros DESC, segundos DESC, terceiros DESC
        ");
        $stmt->execute(['ano' => $ano]);
        return $stmt->fetchAll();
    }
}