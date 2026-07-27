<h1 class="h3 mb-4">Departamentos</h1>

<div class="card mb-4">
    <div class="card-body">
        <h2 class="h6">Novo departamento</h2>
        <form method="POST" action="/departamentos" class="row g-2">
            <div class="col-auto">
                <input type="text" name="nome" class="form-control" placeholder="Nome do departamento" required>
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
        <?php foreach ($departamentos as $departamento): ?>
        <tr>
            <td><?= htmlspecialchars($departamento['nome']) ?></td>
            <td class="text-end">
                <a href="/departamentos/editar?id=<?= $departamento['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                <form method="POST" action="/departamentos/excluir" class="d-inline" onsubmit="return confirm('Excluir este departamento?');">
                    <input type="hidden" name="id" value="<?= $departamento['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($departamentos)): ?>
        <tr><td colspan="2" class="text-muted">Nenhum departamento cadastrado ainda.</td></tr>
        <?php endif; ?>
    </tbody>
</table>