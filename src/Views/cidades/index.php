<h1 class="h3 mb-4">Cidades</h1>

<div class="card mb-4">
    <div class="card-body">
        <h2 class="h6">Nova cidade</h2>
        <form method="POST" action="/cidades" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label">Nome</label>
                <input type="text" name="nome" class="form-control" required>
            </div>
            <div class="col-auto">
                <label class="form-label">UF</label>
                <input type="text" name="uf" maxlength="2" class="form-control" style="width: 80px;" required>
            </div>
            <div class="col-auto">
                <label class="form-label">Regional</label>
                <select name="regional_id" class="form-select">
                    <option value="">— Sem regional —</option>
                    <?php foreach ($regionais as $regional): ?>
                        <option value="<?= $regional['id'] ?>"><?= htmlspecialchars($regional['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Adicionar</button>
            </div>
        </form>
    </div>
</div>

<table class="table table-striped bg-white">
    <thead>
        <tr>
            <th>Nome</th>
            <th>UF</th>
            <th>Regional</th>
            <th class="text-end">Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($cidades as $cidade): ?>
        <tr>
            <td><?= htmlspecialchars($cidade['nome']) ?></td>
            <td><?= htmlspecialchars($cidade['uf']) ?></td>
            <td><?= htmlspecialchars($cidade['regional_nome'] ?? '—') ?></td>
            <td class="text-end">
                <a href="/cidades/editar?id=<?= $cidade['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                <form method="POST" action="/cidades/excluir" class="d-inline" onsubmit="return confirm('Excluir esta cidade?');">
                    <input type="hidden" name="id" value="<?= $cidade['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($cidades)): ?>
        <tr><td colspan="4" class="text-muted">Nenhuma cidade cadastrada ainda.</td></tr>
        <?php endif; ?>
    </tbody>
</table>