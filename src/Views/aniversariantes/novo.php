<h1 class="h3 mb-4">Gerar Aniversariantes do Mês</h1>

<?php if (!empty($_SESSION['erro_aniversariantes'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['erro_aniversariantes']) ?></div>
    <?php unset($_SESSION['erro_aniversariantes']); ?>
<?php endif; ?>

<p class="text-muted">
    Escolha o mês e primeiro você vai poder conferir quem entra na lista antes de gerar as artes.
    Só entram colaboradores <strong>ativos</strong>.
</p>

<form method="GET" action="/aniversariantes/pesquisar" style="max-width: 500px;">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Mês *</label>
            <select name="mes" class="form-select" required>
                <?php
                $meses = [1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'];
                // Sugere o PRÓXIMO mês por padrão: a ideia é já preparar as artes
                // antes do mês virar, não só no dia a dia dele.
                $mesSugerido = ((int) date('n') % 12) + 1;
                foreach ($meses as $numero => $nome):
                ?>
                    <option value="<?= $numero ?>" <?= $numero === $mesSugerido ? 'selected' : '' ?>><?= $nome ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Ano de referência (só pra exibir na arte) *</label>
            <?php
            // Se sugerimos dezembro -> janeiro, o ano de referência já deve virar o próximo
            $anoSugerido = $mesSugerido === 1 && (int) date('n') === 12 ? ((int) date('Y') + 1) : (int) date('Y');
            ?>
            <input type="number" name="ano" value="<?= $anoSugerido ?>" min="2020" max="2100" class="form-control" required>
        </div>
    </div>
    <div class="mt-4">
        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Ver quem faz aniversário</button>
        <a href="/dashboard" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>