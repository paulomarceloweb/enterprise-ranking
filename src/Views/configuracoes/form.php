<h1 class="h3 mb-4">Personalização</h1>

<?php if (!empty($_SESSION['sucesso_configuracao'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['sucesso_configuracao']) ?></div>
    <?php unset($_SESSION['sucesso_configuracao']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['erro_configuracao'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['erro_configuracao']) ?></div>
    <?php unset($_SESSION['erro_configuracao']); ?>
<?php endif; ?>

<form method="POST" action="/configuracoes" enctype="multipart/form-data" style="max-width: 600px;">
    <div class="mb-3">
        <label class="form-label">Nome do sistema</label>
        <input type="text" name="nome_sistema" value="<?= htmlspecialchars($config['nome_sistema']) ?>" class="form-control">
        <div class="form-text">Aparece no topo do menu lateral e no título das páginas.</div>
    </div>

    <div class="mb-3">
        <label class="form-label">Logo do painel</label><br>
        <?php if (!empty($config['logo'])): ?>
            <img src="/<?= htmlspecialchars($config['logo']) ?>" style="max-height: 60px; max-width: 220px;" class="mb-2 bg-dark p-2 rounded">
            <br>
        <?php endif; ?>
        <input type="file" name="logo" accept=".png,.jpg,.jpeg,.svg" class="form-control">
        <div class="form-text">PNG, JPG ou SVG, até 2MB. Aparece no menu lateral no lugar do nome do sistema.</div>
    </div>

    <div class="mb-3">
        <label class="form-label">Ícone / Favicon</label><br>
        <?php if (!empty($config['favicon'])): ?>
            <img src="/<?= htmlspecialchars($config['favicon']) ?>" style="width: 32px; height: 32px; object-fit: contain;" class="mb-2">
            <br>
        <?php endif; ?>
        <input type="file" name="favicon" accept=".png,.ico,.svg" class="form-control">
        <div class="form-text">PNG, ICO ou SVG, até 2MB. Aparece na aba do navegador.</div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label">Cor primária (botões, links, destaques)</label>
            <input type="color" name="cor_primaria" value="<?= htmlspecialchars($config['cor_primaria']) ?>" class="form-control form-control-color" title="Escolha a cor primária">
        </div>
        <div class="col-md-6">
            <label class="form-label">Cor da barra lateral</label>
            <input type="color" name="cor_barra_lateral" value="<?= htmlspecialchars($config['cor_barra_lateral']) ?>" class="form-control form-control-color" title="Escolha a cor da barra lateral">
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">Salvar personalização</button>
    </div>
</form>