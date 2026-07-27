<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Colaboradores</h1>
    <a href="/colaboradores/novo" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Novo colaborador</a>
</div>

<form method="GET" action="/colaboradores" class="card card-body bg-light mb-4">
    <div class="row g-2">
        <div class="col-md-3">
            <label class="form-label small mb-1">Buscar</label>
            <input type="text" name="busca" class="form-control form-control-sm" placeholder="Nome, cargo ou e-mail" value="<?= htmlspecialchars($filtros['busca'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">Todos</option>
                <option value="ativo" <?= ($filtros['status'] ?? '') === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                <option value="desligado" <?= ($filtros['status'] ?? '') === 'desligado' ? 'selected' : '' ?>>Desligado</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">Sexo</label>
            <select name="sexo" class="form-select form-select-sm">
                <option value="">Todos</option>
                <option value="masculino" <?= ($filtros['sexo'] ?? '') === 'masculino' ? 'selected' : '' ?>>Masculino</option>
                <option value="feminino" <?= ($filtros['sexo'] ?? '') === 'feminino' ? 'selected' : '' ?>>Feminino</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">Estado civil</label>
            <select name="estado_civil" class="form-select form-select-sm">
                <option value="">Todos</option>
                <option value="solteiro" <?= ($filtros['estado_civil'] ?? '') === 'solteiro' ? 'selected' : '' ?>>Solteiro(a)</option>
                <option value="casado" <?= ($filtros['estado_civil'] ?? '') === 'casado' ? 'selected' : '' ?>>Casado(a)</option>
                <option value="uniao_estavel" <?= ($filtros['estado_civil'] ?? '') === 'uniao_estavel' ? 'selected' : '' ?>>União estável</option>
                <option value="divorciado" <?= ($filtros['estado_civil'] ?? '') === 'divorciado' ? 'selected' : '' ?>>Divorciado(a)</option>
                <option value="viuvo" <?= ($filtros['estado_civil'] ?? '') === 'viuvo' ? 'selected' : '' ?>>Viúvo(a)</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Filhos</label>
            <select name="quantidade_filhos" class="form-select form-select-sm">
                <option value="">Todos</option>
                <option value="sem" <?= ($filtros['quantidade_filhos'] ?? '') === 'sem' ? 'selected' : '' ?>>Sem filhos</option>
                <option value="com" <?= ($filtros['quantidade_filhos'] ?? '') === 'com' ? 'selected' : '' ?>>Com filhos</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label small mb-1">Cidade</label>
            <select name="cidade_id" class="form-select form-select-sm">
                <option value="">Todas</option>
                <?php foreach ($cidades as $cidade): ?>
                    <option value="<?= (int) $cidade['id'] ?>" <?= (string) $cidade['id'] === ($filtros['cidade_id'] ?? '') ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cidade['nome']) ?>/<?= htmlspecialchars($cidade['uf']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Departamento</label>
            <select name="departamento_id" class="form-select form-select-sm">
                <option value="">Todos</option>
                <?php foreach ($departamentos as $departamento): ?>
                    <option value="<?= (int) $departamento['id'] ?>" <?= (string) $departamento['id'] === ($filtros['departamento_id'] ?? '') ? 'selected' : '' ?>>
                        <?= htmlspecialchars($departamento['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Regional</label>
            <select name="regional_id" class="form-select form-select-sm">
                <option value="">Todas</option>
                <?php foreach ($regionais as $regional): ?>
                    <option value="<?= (int) $regional['id'] ?>" <?= (string) $regional['id'] === ($filtros['regional_id'] ?? '') ? 'selected' : '' ?>>
                        <?= htmlspecialchars($regional['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Aniversariantes de</label>
            <select name="aniversariantes_mes" class="form-select form-select-sm">
                <option value="">Qualquer mês</option>
                <?php
                $nomesMeses = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
                foreach ($nomesMeses as $numero => $nome):
                ?>
                    <option value="<?= $numero ?>" <?= (string) $numero === ($filtros['aniversariantes_mes'] ?? '') ? 'selected' : '' ?>><?= $nome ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label small mb-1">Admitidos a partir de</label>
            <input type="date" name="admitidos_desde" class="form-control form-control-sm" value="<?= htmlspecialchars($filtros['admitidos_desde'] ?? '') ?>">
        </div>
    </div>
    <div class="mt-3">
        <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Filtrar</button>
        <a href="/colaboradores" class="btn btn-sm btn-outline-secondary">Limpar filtros</a>
        <button type="submit" formaction="/colaboradores/exportar" class="btn btn-sm btn-outline-success">
            <i class="bi bi-file-earmark-excel"></i> Exportar Excel
        </button>
        <span class="text-muted small ms-2"><?= count($colaboradores) ?> colaborador(es) encontrado(s)</span>
    </div>
</form>

<table class="table table-striped bg-white align-middle">
    <thead>
        <tr>
            <th></th>
            <th>Nome</th>
            <th>Cidade</th>
            <th>Departamento</th>
            <th>Cargo</th>
            <th>Status</th>
            <th class="text-end">Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($colaboradores as $colaborador): ?>
        <tr>
            <td><img src="/<?= htmlspecialchars($colaborador['foto']) ?>" style="width:40px;height:40px;object-fit:cover;border-radius:50%;"></td>
            <td><?= htmlspecialchars($colaborador['nome']) ?></td>
            <td><?= htmlspecialchars($colaborador['cidade_nome'] ?? '—') ?><?= $colaborador['cidade_uf'] ? '/' . htmlspecialchars($colaborador['cidade_uf']) : '' ?></td>
            <td><?= htmlspecialchars($colaborador['departamento_nome'] ?? '—') ?></td>
            <td><?= htmlspecialchars($colaborador['cargo']) ?></td>
            <td>
                <?php if ($colaborador['status'] === 'ativo'): ?>
                    <span class="badge bg-success">Ativo</span>
                <?php else: ?>
                    <span class="badge bg-danger">Desligado</span>
                <?php endif; ?>
            </td>
            <td class="text-end">
                <a href="/colaboradores/editar?id=<?= $colaborador['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($colaboradores)): ?>
        <tr><td colspan="7" class="text-muted">Nenhum colaborador cadastrado ainda.</td></tr>
        <?php endif; ?>
    </tbody>
</table>