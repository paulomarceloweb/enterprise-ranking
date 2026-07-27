<h1 class="h3 mb-1">Ranking — <?= htmlspecialchars($ranking['mes']) ?>/<?= htmlspecialchars($ranking['ano']) ?></h1>

<?php if (!empty($ranking['enviado_em'])): ?>
    <p class="mb-4">
        <span class="badge bg-success"><i class="bi bi-check2-circle"></i> Enviado em <?= date('d/m/Y H:i', strtotime($ranking['enviado_em'])) ?></span>
        <form action="/ranking/desmarcar-enviado" method="POST" class="d-inline ms-2">
            <input type="hidden" name="id" value="<?= (int) $ranking['id'] ?>">
            <button type="submit" class="btn btn-sm btn-link p-0">desmarcar</button>
        </form>
    </p>
<?php else: ?>
    <p class="text-muted mb-4">Artes geradas para este mês.</p>
<?php endif; ?>

<div class="mb-3 d-flex flex-wrap gap-2">
    <a href="/ranking/novo" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Gerar outra arte</a>
    <?php if (!empty($artes)): ?>
        <a href="/ranking/zip?id=<?= (int) $ranking['id'] ?>" class="btn btn-outline-primary">
            <i class="bi bi-file-earmark-zip"></i> Baixar tudo em .zip (por setor)
        </a>
        <a href="/ranking/exportar-xlsx?id=<?= (int) $ranking['id'] ?>" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel"></i> Baixar planilha (.xlsx)
        </a>
    <?php endif; ?>
    <?php if (empty($ranking['enviado_em'])): ?>
        <form action="/ranking/marcar-enviado" method="POST" class="d-inline">
            <input type="hidden" name="id" value="<?= (int) $ranking['id'] ?>">
            <button type="submit" class="btn btn-outline-success">
                <i class="bi bi-send-check"></i> Marcar como enviado
            </button>
        </form>
    <?php endif; ?>
</div>

<div class="row g-3">
    <?php foreach ($artes as $arte): ?>
        <div class="col-md-3">
            <div class="card h-100">
                <img src="/<?= htmlspecialchars($arte['caminho_imagem']) ?>?t=<?= time() ?>" class="card-img-top">
                <div class="card-body">
                    <p class="card-text mb-1"><strong><?= htmlspecialchars($arte['colaborador_nome']) ?></strong></p>
                    <p class="card-text small text-muted mb-2"><?= (int) $arte['colocacao'] ?>º lugar<?= !empty($arte['setor']) ? ' — ' . htmlspecialchars($arte['setor']) : '' ?></p>
                    <div class="d-flex gap-1">
                        <a href="/<?= htmlspecialchars($arte['caminho_imagem']) ?>" download class="btn btn-sm btn-outline-primary flex-fill">
                            <i class="bi bi-download"></i> Baixar
                        </a>
                        <form action="/ranking/regenerar-arte" method="POST" class="flex-fill">
                            <input type="hidden" name="id" value="<?= (int) $arte['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-secondary w-100" title="Refaz a imagem usando a foto/dados atuais do colaborador">
                                <i class="bi bi-arrow-clockwise"></i> Regenerar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($artes)): ?>
        <p class="text-muted">Nenhuma arte gerada ainda para este ranking.</p>
    <?php endif; ?>
</div>