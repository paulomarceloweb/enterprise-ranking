<h1 class="h3 mb-1">Histórico de Ocorrências</h1>
<p class="text-muted mb-4"><a href="/relatorios">← Voltar aos relatórios</a></p>

<form method="GET" action="/relatorios/ocorrencias" class="mb-4 d-flex align-items-end gap-2 flex-wrap">
    <div>
        <label class="form-label small mb-1">De</label>
        <input type="date" name="inicio" class="form-control form-control-sm" value="<?= htmlspecialchars($inicio) ?>">
    </div>
    <div>
        <label class="form-label small mb-1">Até</label>
        <input type="date" name="fim" class="form-control form-control-sm" value="<?= htmlspecialchars($fim) ?>">
    </div>
    <div>
        <label class="form-label small mb-1">Tipo</label>
        <select name="tipo_ocorrencia_id" class="form-select form-select-sm">
            <option value="">Todos</option>
            <?php foreach ($tipos as $tipo): ?>
                <option value="<?= (int) $tipo['id'] ?>" <?= $tipoOcorrenciaId === (int) $tipo['id'] ? 'selected' : '' ?>><?= htmlspecialchars($tipo['nome']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
    <a href="/relatorios/ocorrencias" class="btn btn-sm btn-outline-secondary">Limpar</a>
    <a href="?<?= htmlspecialchars(http_build_query(array_merge($_GET, ['exportar' => 1]))) ?>" class="btn btn-sm btn-outline-success">
        <i class="bi bi-file-earmark-excel"></i> Exportar Excel
    </a>
</form>

<table class="table table-striped bg-white align-middle">
    <thead>
        <tr>
            <th>Data</th>
            <th>Colaborador</th>
            <th>Tipo</th>
            <th>Descrição</th>
            <th>Anexo</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($ocorrencias as $ocorrencia): ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($ocorrencia['data_evento'])) ?></td>
                <td><?= htmlspecialchars($ocorrencia['colaborador_nome'] ?? '—') ?></td>
                <td><?= htmlspecialchars($ocorrencia['tipo_nome'] ?? '—') ?></td>
                <td class="text-muted small"><?= htmlspecialchars($ocorrencia['descricao'] ?? '') ?></td>
                <td>
                    <?php if (!empty($ocorrencia['arquivo'])): ?>
                        <a href="/ocorrencias/anexo?id=<?= (int) $ocorrencia['id'] ?>" class="btn btn-sm btn-outline-secondary py-0"><i class="bi bi-paperclip"></i></a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($ocorrencias)): ?>
            <tr><td colspan="5" class="text-muted">Nenhuma ocorrência encontrada.</td></tr>
        <?php endif; ?>
    </tbody>
</table>