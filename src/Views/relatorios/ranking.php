<h1 class="h3 mb-1">Ranking por Período — <?= (int) $ano ?></h1>
<p class="text-muted mb-4"><a href="/relatorios">← Voltar aos relatórios</a></p>

<form method="GET" action="/relatorios/ranking" class="mb-4 d-flex align-items-end gap-2">
    <div>
        <label class="form-label small mb-1">Ano</label>
        <input type="number" name="ano" class="form-control form-control-sm" value="<?= (int) $ano ?>" style="width: 110px;">
    </div>
    <button type="submit" class="btn btn-sm btn-primary">Ver</button>
    <a href="?<?= htmlspecialchars(http_build_query(['ano' => $ano, 'exportar' => 1])) ?>" class="btn btn-sm btn-outline-success">
        <i class="bi bi-file-earmark-excel"></i> Exportar Excel
    </a>
</form>

<table class="table table-striped bg-white align-middle">
    <thead>
        <tr>
            <th>Colaborador</th>
            <th class="text-end">🥇 1º lugares</th>
            <th class="text-end">🥈 2º lugares</th>
            <th class="text-end">🥉 3º lugares</th>
            <th class="text-end">Total de aparições</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($linhas as $linha): ?>
            <tr>
                <td><?= htmlspecialchars($linha['colaborador_nome'] ?? '—') ?></td>
                <td class="text-end"><?= (int) $linha['primeiros'] ?></td>
                <td class="text-end"><?= (int) $linha['segundos'] ?></td>
                <td class="text-end"><?= (int) $linha['terceiros'] ?></td>
                <td class="text-end"><?= (int) $linha['total_aparicoes'] ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($linhas)): ?>
            <tr><td colspan="5" class="text-muted">Nenhuma arte de ranking gerada nesse ano.</td></tr>
        <?php endif; ?>
    </tbody>
</table>