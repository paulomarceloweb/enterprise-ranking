<h1 class="h3 mb-4">Novo Usuário</h1>

<?php if (!empty($_SESSION['erro_usuario'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['erro_usuario']) ?></div>
    <?php unset($_SESSION['erro_usuario']); ?>
<?php endif; ?>

<form method="POST" action="/usuarios" style="max-width: 500px;">
    <div class="mb-3">
        <label class="form-label">Nome *</label>
        <input type="text" name="nome" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">E-mail *</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Senha *</label>
        <input type="password" name="senha" class="form-control" minlength="6" required>
        <div class="form-text">Mínimo de 6 caracteres.</div>
    </div>
    <div class="mb-3">
        <label class="form-label">Nível de acesso *</label>
        <select name="nivel" class="form-select" required>
            <option value="usuario">Usuário (acesso normal)</option>
            <option value="super_admin">Super Admin (acesso total, inclusive administração)</option>
        </select>
    </div>
    <div class="mt-4">
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="/usuarios" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>