<h1 class="h3 mb-4">Editar Usuário</h1>

<?php if (!empty($_SESSION['erro_usuario'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['erro_usuario']) ?></div>
    <?php unset($_SESSION['erro_usuario']); ?>
<?php endif; ?>

<?php $ehVoceMesmo = (int) $usuario['id'] === \App\Core\Auth::id(); ?>

<form method="POST" action="/usuarios/atualizar" style="max-width: 500px;">
    <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">

    <div class="mb-3">
        <label class="form-label">Nome *</label>
        <input type="text" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">E-mail *</label>
        <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Nova senha</label>
        <input type="password" name="nova_senha" class="form-control" minlength="6" placeholder="Deixe em branco pra manter a atual">
    </div>
    <div class="mb-3">
        <label class="form-label">Nível de acesso *</label>
        <select name="nivel" class="form-select" required <?= $ehVoceMesmo ? 'disabled' : '' ?>>
            <option value="usuario" <?= $usuario['nivel'] === 'usuario' ? 'selected' : '' ?>>Usuário (acesso normal)</option>
            <option value="super_admin" <?= $usuario['nivel'] === 'super_admin' ? 'selected' : '' ?>>Super Admin (acesso total)</option>
        </select>
        <?php if ($ehVoceMesmo): ?>
            <input type="hidden" name="nivel" value="<?= htmlspecialchars($usuario['nivel']) ?>">
            <div class="form-text">Você não pode alterar o próprio nível de acesso.</div>
        <?php endif; ?>
    </div>
    <div class="form-check mb-3">
        <input type="checkbox" name="ativo" value="1" class="form-check-input" id="ativoCheck"
               <?= (int) $usuario['ativo'] === 1 ? 'checked' : '' ?> <?= $ehVoceMesmo ? 'disabled' : '' ?>>
        <label class="form-check-label" for="ativoCheck">Usuário ativo (pode fazer login)</label>
        <?php if ($ehVoceMesmo): ?>
            <input type="hidden" name="ativo" value="1">
        <?php endif; ?>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="/usuarios" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>