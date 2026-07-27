<h1 class="h3 mb-1">Turnover — <?= (int) $ano ?></h1>
<p class="text-muted mb-4"><a href="/relatorios">← Voltar aos relatórios</a></p>

<form method="GET" action="/relatorios/turnover" class="mb-4 d-flex align-items-end gap-2">
    <div>
        <label class="form-label small mb-1">Ano</label>
        <input type="number" name="ano" class="form-control form-control-sm" value="<?= (int) $ano ?>" style="width: 110px;">
    </div>
    <button type="submit" class="btn btn-sm btn-primary">Ver</button>
    <a href="?<?= htmlspecialchars(http_build_query(['ano' => $ano, 'exportar' => 1])) ?>" class="btn btn-sm btn-outline-success">
        <i class="bi bi-file-earmark-excel"></i> Exportar Excel
    </a>
</form>

<?php
$nomesMeses = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
$totalAdmissoes = array_sum(array_column($meses, 'admissoes'));
$totalDesligamentos = array_sum(array_column($meses, 'desligamentos'));
?>

<p class="mb-3">
    <strong><?= $totalAdmissoes ?></strong> admissão(ões) e <strong><?= $totalDesligamentos ?></strong> desligamento(s) em <?= (int) $ano ?>.
</p>

<table class="table table-striped bg-white align-middle">
    <thead>
        <tr>
            <th>Mês</th>
            <th class="text-end">Admissões</th>
            <th class="text-end">Desligamentos</th>
            <th class="text-end">Saldo</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($meses as $numeroMes => $dadosMes): ?>
            <tr>
                <td><?= $nomesMeses[$numeroMes] ?></td>
                <td class="text-end text-success"><?= $dadosMes['admissoes'] ?></td>
                <td class="text-end text-danger"><?= $dadosMes['desligamentos'] ?></td>
                <td class="text-end"><?= $dadosMes['admissoes'] - $dadosMes['desligamentos'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>