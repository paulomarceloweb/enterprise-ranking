<h1 class="h3 mb-4">Registrar Promoção</h1>

<?php if (!empty($_SESSION['erro_colaborador'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['erro_colaborador']) ?></div>
    <?php unset($_SESSION['erro_colaborador']); ?>
<?php endif; ?>

<p class="text-muted">
    Colaborador: <strong><?= htmlspecialchars($colaborador['nome']) ?></strong> —
    cargo atual: <strong><?= htmlspecialchars($colaborador['cargo']) ?></strong>
</p>

<form method="POST" action="/promocoes" style="max-width: 500px;">
    <input type="hidden" name="colaborador_id" value="<?= $colaborador['id'] ?>">

    <div class="mb-3">
        <label class="form-label">Cargo novo *</label>
        <input type="text" name="cargo_novo" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Data da promoção *</label>
        <input type="date" name="data_promocao" value="<?= date('Y-m-d') ?>" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Observação (opcional)</label>
        <textarea name="observacao" class="form-control" rows="3"></textarea>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary"><i class="bi bi-arrow-up-circle"></i> Registrar e gerar arte</button>
        <a href="/colaboradores/editar?id=<?= $colaborador['id'] ?>" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>