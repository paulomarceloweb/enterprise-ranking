<h1 class="h3 mb-1">Registrar Ocorrência</h1>
<p class="text-muted mb-4">Colaborador: <strong><?= htmlspecialchars($colaborador['nome']) ?></strong></p>

<?php if (!empty($_SESSION['erro_ocorrencia'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['erro_ocorrencia']) ?></div>
    <?php unset($_SESSION['erro_ocorrencia']); ?>
<?php endif; ?>

<form method="POST" action="/ocorrencias" enctype="multipart/form-data" style="max-width: 600px;">
    <input type="hidden" name="colaborador_id" value="<?= (int) $colaborador['id'] ?>">

    <div class="mb-3">
        <label class="form-label">Tipo de ocorrência *</label>
        <select name="tipo_ocorrencia_id" class="form-select" required>
            <option value="">Selecione...</option>
            <?php foreach ($tipos as $tipo): ?>
                <option value="<?= (int) $tipo['id'] ?>"><?= htmlspecialchars($tipo['nome']) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (empty($tipos)): ?>
            <div class="form-text text-warning">Nenhum tipo de ocorrência cadastrado ainda. <a href="/tipos-ocorrencia">Cadastre um aqui</a> antes de continuar.</div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label class="form-label">Data da ocorrência *</label>
        <input type="date" name="data_ocorrencia" class="form-control" value="<?= date('Y-m-d') ?>" required style="max-width: 220px;">
    </div>

    <div class="mb-3">
        <label class="form-label">Descrição</label>
        <textarea name="descricao" class="form-control" rows="4" placeholder="Detalhes da ocorrência (opcional)"></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">Anexo (PDF, DOC, DOCX, XLS ou XLSX — até 10MB)</label>
        <input type="file" name="anexo" accept=".pdf,.doc,.docx,.xls,.xlsx" class="form-control">
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Registrar</button>
        <a href="/colaboradores/editar?id=<?= (int) $colaborador['id'] ?>" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>