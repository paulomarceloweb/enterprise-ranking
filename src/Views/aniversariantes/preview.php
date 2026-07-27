<?php
$meses = [1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'];
$nomeMes = $meses[$mes] ?? '';
?>

<h1 class="h3 mb-1">Aniversariantes de <?= htmlspecialchars($nomeMes) ?>/<?= (int) $ano ?></h1>

<?php if (empty($colaboradores)): ?>
    <div class="alert alert-warning mt-3">
        Nenhum colaborador ativo faz aniversário em <?= htmlspecialchars($nomeMes) ?>.
    </div>
    <a href="/aniversariantes/novo" class="btn btn-outline-secondary">Escolher outro mês</a>
<?php else: ?>

    <p class="text-muted mb-4">
        <?= count($colaboradores) ?> colaborador(es) encontrado(s) —
        serão geradas <strong><?= $totalPaginas ?></strong> imagem(ns)
        (agrupadas de até 6 pessoas cada, na ordem do dia do aniversário).
    </p>

    <table class="table table-striped bg-white align-middle">
        <thead>
            <tr>
                <th></th>
                <th>Nome</th>
                <th>Cargo</th>
                <th>Cidade</th>
                <th>Aniversário</th>
                <th>Vai na imagem</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($colaboradores as $indice => $colaborador): ?>
                <tr>
                    <td><img src="/<?= htmlspecialchars($colaborador['foto']) ?>" style="width:36px;height:36px;object-fit:cover;border-radius:50%;"></td>
                    <td><?= htmlspecialchars($colaborador['nome']) ?></td>
                    <td><?= htmlspecialchars($colaborador['cargo']) ?></td>
                    <td><?= htmlspecialchars($colaborador['cidade_nome'] ?? '—') ?><?= $colaborador['cidade_uf'] ? '/' . htmlspecialchars($colaborador['cidade_uf']) : '' ?></td>
                    <td><?= date('d/m', strtotime($colaborador['data_nascimento'])) ?></td>
                    <td class="text-muted small">Imagem <?= (int) ($indice / 6) + 1 ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <form method="POST" action="/aniversariantes">
        <input type="hidden" name="mes" value="<?= (int) $mes ?>">
        <input type="hidden" name="ano" value="<?= (int) $ano ?>">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-balloon"></i> Confirmar e gerar <?= $totalPaginas ?> imagem(ns)
        </button>
        <a href="/aniversariantes/novo" class="btn btn-outline-secondary">Voltar</a>
    </form>

<?php endif; ?>