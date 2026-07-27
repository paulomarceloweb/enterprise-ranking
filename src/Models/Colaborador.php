<?php

namespace App\Models;

use App\Core\Database;

class Colaborador
{
    /**
     * Lista colaboradores com filtros opcionais. Todos os filtros são
     * combinados com AND. Chaves aceitas em $filtros:
     * - busca            (procura em nome, cargo e e-mail)
     * - status           ('ativo' | 'desligado')
     * - sexo             ('masculino' | 'feminino')
     * - estado_civil
     * - quantidade_filhos ('sem' | 'com' | número exato)
     * - cidade_id
     * - departamento_id
     * - regional_id
     * - aniversariantes_mes (1-12: nascidos nesse mês)
     * - admitidos_desde   (data_admissao >= essa data)
     */
    public static function listar(array $filtros = []): array
    {
        $pdo = Database::getConnection();

        $condicoes = [];
        $parametros = [];

        if (!empty($filtros['busca'])) {
            // Placeholders repetidos ($busca usado 3x) dão erro "Invalid parameter
            // number" em PDO sem emulação de prepares — por isso 3 nomes distintos.
            $condicoes[] = '(colaboradores.nome LIKE :busca_nome OR colaboradores.cargo LIKE :busca_cargo OR colaboradores.email LIKE :busca_email)';
            $termoBusca = '%' . $filtros['busca'] . '%';
            $parametros['busca_nome'] = $termoBusca;
            $parametros['busca_cargo'] = $termoBusca;
            $parametros['busca_email'] = $termoBusca;
        }

        if (!empty($filtros['status'])) {
            $condicoes[] = 'colaboradores.status = :status';
            $parametros['status'] = $filtros['status'];
        }

        if (!empty($filtros['sexo'])) {
            $condicoes[] = 'colaboradores.sexo = :sexo';
            $parametros['sexo'] = $filtros['sexo'];
        }

        if (!empty($filtros['estado_civil'])) {
            $condicoes[] = 'colaboradores.estado_civil = :estado_civil';
            $parametros['estado_civil'] = $filtros['estado_civil'];
        }

        if (isset($filtros['quantidade_filhos']) && $filtros['quantidade_filhos'] !== '') {
            if ($filtros['quantidade_filhos'] === 'sem') {
                $condicoes[] = '(colaboradores.quantidade_filhos IS NULL OR colaboradores.quantidade_filhos = 0)';
            } elseif ($filtros['quantidade_filhos'] === 'com') {
                $condicoes[] = 'colaboradores.quantidade_filhos > 0';
            } else {
                $condicoes[] = 'colaboradores.quantidade_filhos = :quantidade_filhos';
                $parametros['quantidade_filhos'] = (int) $filtros['quantidade_filhos'];
            }
        }

        if (!empty($filtros['cidade_id'])) {
            $condicoes[] = 'colaboradores.cidade_id = :cidade_id';
            $parametros['cidade_id'] = (int) $filtros['cidade_id'];
        }

        if (!empty($filtros['departamento_id'])) {
            $condicoes[] = 'colaboradores.departamento_id = :departamento_id';
            $parametros['departamento_id'] = (int) $filtros['departamento_id'];
        }

        if (!empty($filtros['regional_id'])) {
            $condicoes[] = 'cidades.regional_id = :regional_id';
            $parametros['regional_id'] = (int) $filtros['regional_id'];
        }

        if (!empty($filtros['aniversariantes_mes'])) {
            $condicoes[] = 'MONTH(colaboradores.data_nascimento) = :aniversariantes_mes';
            $parametros['aniversariantes_mes'] = (int) $filtros['aniversariantes_mes'];
        }

        if (!empty($filtros['admitidos_desde'])) {
            $condicoes[] = 'colaboradores.data_admissao >= :admitidos_desde';
            $parametros['admitidos_desde'] = $filtros['admitidos_desde'];
        }

        $sql = '
            SELECT colaboradores.*, cidades.nome AS cidade_nome, cidades.uf AS cidade_uf,
                   departamentos.nome AS departamento_nome, regionais.nome AS regional_nome
            FROM colaboradores
            LEFT JOIN cidades ON cidades.id = colaboradores.cidade_id
            LEFT JOIN departamentos ON departamentos.id = colaboradores.departamento_id
            LEFT JOIN regionais ON regionais.id = cidades.regional_id
        ';

        if (!empty($condicoes)) {
            $sql .= ' WHERE ' . implode(' AND ', $condicoes);
        }

        $sql .= ' ORDER BY colaboradores.nome';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }

    public static function buscarPorId(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT colaboradores.*, cidades.nome AS cidade_nome, cidades.uf AS cidade_uf,
                   departamentos.nome AS departamento_nome, regionais.nome AS regional_nome
            FROM colaboradores
            LEFT JOIN cidades ON cidades.id = colaboradores.cidade_id
            LEFT JOIN departamentos ON departamentos.id = colaboradores.departamento_id
            LEFT JOIN regionais ON regionais.id = cidades.regional_id
            WHERE colaboradores.id = :id
        ');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Busca um colaborador pelo nome, usada na geração em lote pra casar
     * os nomes da planilha com os cadastros existentes.
     *
     * 1º tenta match exato (sem diferenciar maiúsculas/espaços extras).
     * 2º se não achar, tenta "começa com" (útil quando a planilha só tem o
     *    primeiro nome, tipo "Ana" pra "Ana Boer") — mas só aceita se achar
     *    exatamente 1 resultado, pra não casar errado quando é ambíguo.
     */
    public static function buscarPorNome(string $nome): ?array
    {
        $pdo = Database::getConnection();
        $nomeLimpo = trim($nome);

        $stmt = $pdo->prepare('
            SELECT colaboradores.*, cidades.nome AS cidade_nome, cidades.uf AS cidade_uf,
                   departamentos.nome AS departamento_nome, regionais.nome AS regional_nome
            FROM colaboradores
            LEFT JOIN cidades ON cidades.id = colaboradores.cidade_id
            LEFT JOIN departamentos ON departamentos.id = colaboradores.departamento_id
            LEFT JOIN regionais ON regionais.id = cidades.regional_id
            WHERE LOWER(TRIM(colaboradores.nome)) = LOWER(:nome)
            LIMIT 1
        ');
        $stmt->execute(['nome' => $nomeLimpo]);
        $encontrado = $stmt->fetch();
        if ($encontrado) {
            return $encontrado;
        }

        $stmt = $pdo->prepare('
            SELECT colaboradores.*, cidades.nome AS cidade_nome, cidades.uf AS cidade_uf,
                   departamentos.nome AS departamento_nome, regionais.nome AS regional_nome
            FROM colaboradores
            LEFT JOIN cidades ON cidades.id = colaboradores.cidade_id
            LEFT JOIN departamentos ON departamentos.id = colaboradores.departamento_id
            LEFT JOIN regionais ON regionais.id = cidades.regional_id
            WHERE LOWER(colaboradores.nome) LIKE LOWER(:nome)
        ');
        $stmt->execute(['nome' => $nomeLimpo . ' %']);
        $candidatos = $stmt->fetchAll();

        return count($candidatos) === 1 ? $candidatos[0] : null;
    }

    public static function criar(array $dados): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            INSERT INTO colaboradores
                (nome, foto, sexo, estado_civil, quantidade_filhos, cidade_id, departamento_id, cargo,
                 data_admissao, data_nascimento, status, data_desligamento, telefone, email, instagram, facebook, observacoes)
            VALUES
                (:nome, :foto, :sexo, :estado_civil, :quantidade_filhos, :cidade_id, :departamento_id, :cargo,
                 :data_admissao, :data_nascimento, :status, :data_desligamento, :telefone, :email, :instagram, :facebook, :observacoes)
        ');
        $stmt->execute($dados);
        return (int) $pdo->lastInsertId();
    }

    public static function atualizar(int $id, array $dados): void
    {
        $pdo = Database::getConnection();
        $dados['id'] = $id;
        $stmt = $pdo->prepare('
            UPDATE colaboradores SET
                nome = :nome, sexo = :sexo, estado_civil = :estado_civil, quantidade_filhos = :quantidade_filhos,
                cidade_id = :cidade_id, departamento_id = :departamento_id,
                cargo = :cargo, data_admissao = :data_admissao, data_nascimento = :data_nascimento,
                status = :status, data_desligamento = :data_desligamento,
                telefone = :telefone, email = :email, instagram = :instagram, facebook = :facebook,
                observacoes = :observacoes
            WHERE id = :id
        ');
        $stmt->execute($dados);
    }

    public static function atualizarFoto(int $id, string $foto): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE colaboradores SET foto = :foto WHERE id = :id');
        $stmt->execute(['foto' => $foto, 'id' => $id]);
    }

    public static function excluir(int $id): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM colaboradores WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}