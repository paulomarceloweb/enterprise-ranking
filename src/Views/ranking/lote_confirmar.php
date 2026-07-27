<h1 class="h3 mb-1">Confirmar geração em lote</h1>
<p class="text-muted mb-4">Arquivo enviado: <strong><?= htmlspecialchars($_FILES['planilha']['name'] ?? '') ?></strong></p>

<form method="POST" action="/ranking/lote/processar" style="max-width: 500px;">
    <input type="hidden" name="arquivo_temp" value="<?= htmlspecialchars($nomeTemp) ?>">

    <div class="mb-3">
        <label class="form-label">Aba (mês) da planilha *</label>
        <select name="aba" class="form-select" required>
            <?php foreach ($abas as $aba): ?>
                <option value="<?= htmlspecialchars($aba) ?>" <?= $aba === $abaSugerida ? 'selected' : '' ?>>
                    <?= htmlspecialchars($aba) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div class="form-text">Escolhemos a última aba da planilha por padrão — confira se é o mês certo.</div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Mês de referência *</label>
            <select name="mes" class="form-select" required>
                <?php
                $meses = [1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'];
                $mesAtual = (int) date('n');
                foreach ($meses as $numero => $nome):
                ?>
                    <option value="<?= $numero ?>" <?= $numero === $mesAtual ? 'selected' : '' ?>><?= $nome ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Ano *</label>
            <input type="number" name="ano" value="<?= date('Y') ?>" min="2020" max="2100" class="form-control" required>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary"><i class="bi bi-images"></i> Gerar todas as artes</button>
        <a href="/ranking/lote" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>