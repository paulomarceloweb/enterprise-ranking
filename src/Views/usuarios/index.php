<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Usuários</h1>
    <a href="/usuarios/novo" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Novo usuário</a>
</div>

<?php if (!empty($_SESSION['erro_usuario'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['erro_usuario']) ?></div>
    <?php unset($_SESSION['erro_usuario']); ?>
<?php endif; ?>

<table class="table table-striped bg-white align-middle">
    <thead>
        <tr>
            <th>Nome</th>
            <th>E-mail</th>
            <th>Nível</th>
            <th>Status</th>
            <th class="text-end">Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($usuarios as $usuario): ?>
            <tr>
                <td><?= htmlspecialchars($usuario['nome']) ?><?= (int) $usuario['id'] === \App\Core\Auth::id() ? ' <span class="badge bg-secondary">você</span>' : '' ?></td>
                <td><?= htmlspecialchars($usuario['email']) ?></td>
                <td><?= $usuario['nivel'] === 'super_admin' ? '<span class="badge bg-dark">Super Admin</span>' : '<span class="badge bg-light text-dark">Usuário</span>' ?></td>
                <td><?= (int) $usuario['ativo'] === 1 ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-danger">Inativo</span>' ?></td>
                <td class="text-end">
                    <a href="/usuarios/editar?id=<?= (int) $usuario['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                    <?php if ((int) $usuario['id'] !== \App\Core\Auth::id()): ?>
                        <form method="POST" action="/usuarios/excluir" class="d-inline" onsubmit="return confirm('Excluir este usuário definitivamente?');">
                            <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($usuarios)): ?>
            <tr><td colspan="5" class="text-muted">Nenhum usuário cadastrado.</td></tr>
        <?php endif; ?>
    </tbody>
</table>