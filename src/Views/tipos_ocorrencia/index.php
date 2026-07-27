<h1 class="h3 mb-4">Tipos de Ocorrência</h1>

<div class="card mb-4">
    <div class="card-body">
        <h2 class="h6">Novo tipo</h2>
        <form method="POST" action="/tipos-ocorrencia" class="row g-2">
            <div class="col-auto">
                <input type="text" name="nome" class="form-control" placeholder="Ex: PMO, PMC, Advertência, Elogio" required>
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
            <th class="text-end">Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tipos as $tipo): ?>
        <tr>
            <td><?= htmlspecialchars($tipo['nome']) ?></td>
            <td class="text-end">
                <a href="/tipos-ocorrencia/editar?id=<?= $tipo['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                <form method="POST" action="/tipos-ocorrencia/excluir" class="d-inline" onsubmit="return confirm('Excluir este tipo?');">
                    <input type="hidden" name="id" value="<?= $tipo['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($tipos)): ?>
        <tr><td colspan="2" class="text-muted">Nenhum tipo cadastrado ainda.</td></tr>
        <?php endif; ?>
    </tbody>
</table>