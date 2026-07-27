<h1 class="h3 mb-4">Editar Cidade</h1>
<form method="POST" action="/cidades/atualizar" style="max-width: 400px;">
    <input type="hidden" name="id" value="<?= $cidade['id'] ?>">
    <div class="mb-3">
        <label class="form-label">Nome</label>
        <input type="text" name="nome" value="<?= htmlspecialchars($cidade['nome']) ?>" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">UF</label>
        <input type="text" name="uf" maxlength="2" value="<?= htmlspecialchars($cidade['uf']) ?>" class="form-control" style="width: 80px;" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Regional</label>
        <select name="regional_id" class="form-select">
            <option value="">— Sem regional —</option>
            <?php foreach ($regionais as $regional): ?>
                <option value="<?= $regional['id'] ?>" <?= $regional['id'] == $cidade['regional_id'] ? 'selected' : '' ?>><?= htmlspecialchars($regional['nome']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Salvar</button>
    <a href="/cidades" class="btn btn-outline-secondary">Cancelar</a>
</form>