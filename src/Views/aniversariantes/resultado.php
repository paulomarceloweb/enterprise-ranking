<h1 class="h3 mb-4">Aniversariantes gerados</h1>

<div class="row g-3">
    <?php foreach ($artes as $arte): ?>
        <?php if (!$arte) continue; ?>
        <div class="col-md-4">
            <div class="card h-100">
                <img src="/<?= htmlspecialchars($arte['caminho_imagem']) ?>" class="card-img-top">
                <div class="card-body">
                    <a href="/<?= htmlspecialchars($arte['caminho_imagem']) ?>" download class="btn btn-sm btn-outline-primary w-100">
                        <i class="bi bi-download"></i> Baixar
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<a href="/aniversariantes/novo" class="btn btn-outline-secondary mt-4">Gerar outro mês</a>