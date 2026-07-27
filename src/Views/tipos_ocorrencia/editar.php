<h1 class="h3 mb-4">Editar Tipo de Ocorrência</h1>
<form method="POST" action="/tipos-ocorrencia/atualizar" style="max-width: 400px;">
    <input type="hidden" name="id" value="<?= $tipo['id'] ?>">
    <div class="mb-3">
        <label class="form-label">Nome</label>
        <input type="text" name="nome" value="<?= htmlspecialchars($tipo['nome']) ?>" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Salvar</button>
    <a href="/tipos-ocorrencia" class="btn btn-outline-secondary">Cancelar</a>
</form>