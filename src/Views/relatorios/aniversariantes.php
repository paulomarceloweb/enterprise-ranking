<h1 class="h3 mb-1">Aniversariantes Futuros</h1>
<p class="text-muted mb-4"><a href="/relatorios">← Voltar aos relatórios</a></p>

<form method="GET" action="/relatorios/aniversariantes" class="mb-4 d-flex align-items-end gap-2">
    <div>
        <label class="form-label small mb-1">Próximos quantos dias?</label>
        <select name="dias" class="form-select form-select-sm">
            <?php foreach ([7, 15, 30, 60, 90] as $opcao): ?>
                <option value="<?= $opcao ?>" <?= $dias === $opcao ? 'selected' : '' ?>><?= $opcao ?> dias</option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
</form>

<table class="table table-striped bg-white align-middle">
    <thead>
        <tr>
            <th></th>
            <th>Nome</th>
            <th>Data de nascimento</th>
            <th>Próximo aniversário</th>
            <th>Departamento</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($colaboradores as $colaborador): ?>
            <tr>
                <td><img src="/<?= htmlspecialchars($colaborador['foto']) ?>" style="width:36px;height:36px;object-fit:cover;border-radius:50%;"></td>
                <td><?= htmlspecialchars($colaborador['nome']) ?></td>
                <td><?= date('d/m', strtotime($colaborador['data_nascimento'])) ?></td>
                <td><?= date('d/m/Y', strtotime($colaborador['proximo_aniversario'])) ?></td>
                <td><?= htmlspecialchars($colaborador['departamento_nome'] ?? '—') ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($colaboradores)): ?>
            <tr><td colspan="5" class="text-muted">Nenhum aniversariante nesse período.</td></tr>
        <?php endif; ?>
    </tbody>
</table>