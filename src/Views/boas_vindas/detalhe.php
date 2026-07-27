<h1 class="h3 mb-4">Boas-vindas — <?= htmlspecialchars($colaborador['nome'] ?? '') ?></h1>

<div class="row">
    <div class="col-md-5">
        <img src="/<?= htmlspecialchars($arte['caminho_imagem']) ?>" class="img-fluid rounded shadow-sm">
        <div class="mt-3 d-flex gap-2">
            <a href="/<?= htmlspecialchars($arte['caminho_imagem']) ?>" download class="btn btn-primary">
                <i class="bi bi-download"></i> Baixar imagem
            </a>
            <a href="/colaboradores/editar?id=<?= (int) $colaborador['id'] ?>" class="btn btn-outline-secondary">
                Voltar pro colaborador
            </a>
        </div>
    </div>
</div>