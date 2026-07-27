<h1 class="h3 mb-4">Gerar Ranking — Individual</h1>

<?php if (!empty($_SESSION['erro_ranking'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['erro_ranking']) ?></div>
    <?php unset($_SESSION['erro_ranking']); ?>
<?php endif; ?>

<form method="POST" action="/ranking" style="max-width: 500px;">
    <div class="mb-3">
        <label class="form-label">Colaborador *</label>
        <select name="colaborador_id" class="form-select" required>
            <option value="">Selecione</option>
            <?php foreach ($colaboradores as $colaborador): ?>
                <option value="<?= $colaborador['id'] ?>">
                    <?= htmlspecialchars($colaborador['nome']) ?>
                    — <?= htmlspecialchars($colaborador['cidade_nome'] ?? '') ?>/<?= htmlspecialchars($colaborador['cidade_uf'] ?? '') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Colocação *</label>
        <select name="colocacao" class="form-select" required>
            <?php for ($i = 1; $i <= 10; $i++): ?>
                <option value="<?= $i ?>"><?= $i ?>º lugar</option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Mês *</label>
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
        <button type="submit" class="btn btn-primary"><i class="bi bi-image"></i> Gerar arte</button>
        <a href="/ranking" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>