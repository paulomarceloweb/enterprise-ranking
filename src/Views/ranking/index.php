<h1 class="h3 mb-4">Ranking</h1>

<div class="mb-3 d-flex flex-wrap gap-2">
    <a href="/ranking/novo" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Gerar arte individual</a>
    <a href="/ranking/lote" class="btn btn-outline-primary"><i class="bi bi-file-earmark-spreadsheet"></i> Gerar em lote (planilha)</a>
    <a href="/ranking/manual/novo" class="btn btn-outline-primary"><i class="bi bi-list-check"></i> Montar ranking manualmente</a>
    <a href="/ranking/planilhas" class="btn btn-outline-secondary"><i class="bi bi-clock-history"></i> Planilhas enviadas</a>
</div>

<table class="table table-striped align-middle">
    <thead>
        <tr>
            <th>Mês/Ano</th>
            <th>Criado por</th>
            <th>Artes geradas</th>
            <th>Status</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rankings as $ranking): ?>
            <tr>
                <td><?= htmlspecialchars($ranking['mes']) ?>/<?= htmlspecialchars($ranking['ano']) ?></td>
                <td><?= htmlspecialchars($ranking['usuario_nome'] ?? '-') ?></td>
                <td><?= (int) $ranking['total_artes'] ?></td>
                <td>
                    <?php if (!empty($ranking['enviado_em'])): ?>
                        <span class="badge bg-success">Enviado</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Pendente</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="/ranking/detalhe?id=<?= $ranking['id'] ?>" class="btn btn-sm btn-outline-primary">Ver artes</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($rankings)): ?>
            <tr><td colspan="5" class="text-muted">Nenhum ranking gerado ainda.</td></tr>
        <?php endif; ?>
    </tbody>
</table>