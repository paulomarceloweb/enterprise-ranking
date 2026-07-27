<h1 class="h3 mb-1">Histórico de Promoções</h1>
<p class="text-muted mb-4"><a href="/relatorios">← Voltar aos relatórios</a></p>

<form method="GET" action="/relatorios/promocoes" class="mb-4 d-flex align-items-end gap-2">
    <div>
        <label class="form-label small mb-1">De</label>
        <input type="date" name="inicio" class="form-control form-control-sm" value="<?= htmlspecialchars($inicio) ?>">
    </div>
    <div>
        <label class="form-label small mb-1">Até</label>
        <input type="date" name="fim" class="form-control form-control-sm" value="<?= htmlspecialchars($fim) ?>">
    </div>
    <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
    <a href="/relatorios/promocoes" class="btn btn-sm btn-outline-secondary">Limpar</a>
    <a href="?<?= htmlspecialchars(http_build_query(array_merge($_GET, ['exportar' => 1]))) ?>" class="btn btn-sm btn-outline-success">
        <i class="bi bi-file-earmark-excel"></i> Exportar Excel
    </a>
</form>

<table class="table table-striped bg-white align-middle">
    <thead>
        <tr>
            <th>Data</th>
            <th>Colaborador</th>
            <th>Cargo anterior</th>
            <th>Cargo novo</th>
            <th>Detalhes</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($eventos as $evento): ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($evento['data_evento'])) ?></td>
                <td><?= htmlspecialchars($evento['colaborador_nome'] ?? '—') ?></td>
                <td><?= htmlspecialchars($evento['cargo_anterior'] ?? '—') ?></td>
                <td><?= htmlspecialchars($evento['cargo_novo'] ?? '—') ?></td>
                <td class="text-muted small"><?= htmlspecialchars($evento['detalhes'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($eventos)): ?>
            <tr><td colspan="5" class="text-muted">Nenhuma promoção registrada nesse período.</td></tr>
        <?php endif; ?>
    </tbody>
</table>