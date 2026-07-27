<h1 class="h3 mb-4">Editar Departamento</h1>
<form method="POST" action="/departamentos/atualizar" style="max-width: 400px;">
    <input type="hidden" name="id" value="<?= $departamento['id'] ?>">
    <div class="mb-3">
        <label class="form-label">Nome</label>
        <input type="text" name="nome" value="<?= htmlspecialchars($departamento['nome']) ?>" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Salvar</button>
    <a href="/departamentos" class="btn btn-outline-secondary">Cancelar</a>
</form>