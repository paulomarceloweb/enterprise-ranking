<h1 class="h3 mb-4">Regionais</h1>

<div class="card mb-4">
    <div class="card-body">
        <h2 class="h6">Nova regional</h2>
        <form method="POST" action="/regionais" class="row g-2">
            <div class="col-auto">
                <input type="text" name="nome" class="form-control" placeholder="Nome da regional" required>
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
        <?php foreach ($regionais as $regional): ?>
        <tr>
            <td><?= htmlspecialchars($regional['nome']) ?></td>
            <td class="text-end">
                <a href="/regionais/editar?id=<?= $regional['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                <form method="POST" action="/regionais/excluir" class="d-inline" onsubmit="return confirm('Excluir esta regional?');">
                    <input type="hidden" name="id" value="<?= $regional['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($regionais)): ?>
        <tr><td colspan="2" class="text-muted">Nenhuma regional cadastrada ainda.</td></tr>
        <?php endif; ?>
    </tbody>
</table>