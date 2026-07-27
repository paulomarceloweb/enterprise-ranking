<h1 class="h3 mb-1">Resultado do lote</h1>
<p class="text-muted mb-4">Aba processada: <strong><?= htmlspecialchars($resultado['aba']) ?></strong></p>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card text-center p-3">
            <div class="h2 mb-0"><?= (int) $resultado['total'] ?></div>
            <div class="text-muted small">Entradas na planilha</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center p-3 border-success">
            <div class="h2 mb-0 text-success"><?= (int) $resultado['gerados'] ?></div>
            <div class="text-muted small">Artes geradas com sucesso</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center p-3 <?= count($resultado['nao_encontrados']) > 0 ? 'border-warning' : '' ?>">
            <div class="h2 mb-0 <?= count($resultado['nao_encontrados']) > 0 ? 'text-warning' : '' ?>">
                <?= count($resultado['nao_encontrados']) ?>
            </div>
            <div class="text-muted small">Não encontrados / com erro</div>
        </div>
    </div>
</div>

<?php if (!empty($resultado['nao_encontrados'])): ?>
    <div class="alert alert-warning">
        <strong>Esses nomes da planilha não bateram com nenhum colaborador cadastrado</strong>
        (ou o nome está ambíguo/incompleto). Confira a grafia, cadastre quem faltar em
        <a href="/colaboradores/novo">Colaboradores</a> e gere a arte individual depois:
        <ul class="mb-0 mt-2">
            <?php foreach ($resultado['nao_encontrados'] as $item): ?>
                <li><?= htmlspecialchars($item) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<a href="/ranking/detalhe?id=<?= (int) $resultado['ranking_id'] ?>" class="btn btn-primary">
    <i class="bi bi-images"></i> Ver artes geradas
</a>
<a href="/ranking/lote" class="btn btn-outline-secondary">Processar outra planilha</a>