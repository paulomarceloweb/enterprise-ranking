<h1 class="h3 mb-4">Gerar Ranking — Em Lote</h1>

<?php if (!empty($_SESSION['erro_lote'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['erro_lote']) ?></div>
    <?php unset($_SESSION['erro_lote']); ?>
<?php endif; ?>

<p class="text-muted">
    Envie a planilha de ranking (.xlsx). No próximo passo você escolhe qual aba (mês) processar.
</p>

<form method="POST" action="/ranking/lote/upload" enctype="multipart/form-data" style="max-width: 500px;">
    <div class="mb-3">
        <label class="form-label">Planilha (.xlsx) *</label>
        <input type="file" name="planilha" accept=".xlsx" class="form-control" required>
    </div>
    <div class="mt-4">
        <button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Enviar e escolher aba</button>
        <a href="/ranking" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>

<p class="text-muted mt-4">
    Prefere não usar planilha? <a href="/ranking/manual/novo">Monte o ranking manualmente pelo sistema</a>.
</p>