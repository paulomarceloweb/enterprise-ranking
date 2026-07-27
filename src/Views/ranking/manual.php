<h1 class="h3 mb-1">Gerar Ranking — Cadastro Manual</h1>
<p class="text-muted mb-4">Monte o ranking do mês linha a linha, sem precisar de planilha. Dá pra ter empate: só adicionar mais de uma linha com a mesma colocação.</p>

<?php if (!empty($_SESSION['erro_lote'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['erro_lote']) ?></div>
    <?php unset($_SESSION['erro_lote']); ?>
<?php endif; ?>

<form action="/ranking/manual/gerar" method="POST" id="form-manual">
    <div class="row g-3 mb-4">
        <div class="col-auto">
            <label class="form-label">Mês</label>
            <select name="mes" class="form-select" required>
                <?php
                $nomesMeses = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
                foreach ($nomesMeses as $numero => $nome):
                ?>
                    <option value="<?= $numero ?>" <?= $numero === $mesSugerido ? 'selected' : '' ?>><?= $nome ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <label class="form-label">Ano</label>
            <input type="number" name="ano" class="form-control" value="<?= (int) $anoSugerido ?>" required style="width: 110px;">
        </div>
    </div>

    <table class="table align-middle" id="tabela-linhas">
        <thead>
            <tr>
                <th style="width: 90px;">Colocação</th>
                <th style="width: 220px;">Setor</th>
                <th>Colaborador</th>
                <th style="width: 60px;"></th>
            </tr>
        </thead>
        <tbody>
            <!-- linhas adicionadas via JS -->
        </tbody>
    </table>

    <button type="button" class="btn btn-outline-secondary mb-4" id="btn-add-linha">
        <i class="bi bi-plus-lg"></i> Adicionar linha
    </button>

    <div>
        <button type="submit" class="btn btn-primary" id="btn-gerar">
            <i class="bi bi-image"></i> Gerar artes
        </button>
        <a href="/ranking" class="btn btn-link">Cancelar</a>
    </div>
</form>

<!-- Template de linha (fica escondido, é clonado via JS) -->
<template id="template-linha">
    <tr>
        <td>
            <input type="number" name="colocacao[]" class="form-control" min="1" required>
        </td>
        <td>
            <select name="setor[]" class="form-select" required>
                <option value="">Selecione...</option>
                <?php foreach ($departamentos as $departamento): ?>
                    <option value="<?= htmlspecialchars($departamento['nome']) ?>">
                        <?= htmlspecialchars($departamento['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
        <td>
            <select name="colaborador_id[]" class="form-select" required>
                <option value="">Selecione...</option>
                <?php foreach ($colaboradores as $colaborador): ?>
                    <option value="<?= (int) $colaborador['id'] ?>">
                        <?= htmlspecialchars($colaborador['nome']) ?><?= $colaborador['status'] === 'desligado' ? ' (desligado)' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger btn-remover-linha">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
</template>

<script>
(function () {
    const tabelaBody = document.querySelector('#tabela-linhas tbody');
    const template = document.getElementById('template-linha');
    const btnAdd = document.getElementById('btn-add-linha');

    function adicionarLinha() {
        const clone = template.content.cloneNode(true);
        const btnRemover = clone.querySelector('.btn-remover-linha');
        btnRemover.addEventListener('click', function (evento) {
            evento.target.closest('tr').remove();
        });
        tabelaBody.appendChild(clone);
    }

    btnAdd.addEventListener('click', adicionarLinha);

    // Começa já com 3 linhas em branco pra agilizar
    adicionarLinha();
    adicionarLinha();
    adicionarLinha();

    document.getElementById('form-manual').addEventListener('submit', function (evento) {
        const linhasPreenchidas = tabelaBody.querySelectorAll('tr');
        let temAlgumaLinhaValida = false;
        linhasPreenchidas.forEach(function (linha) {
            const colaboradorSelect = linha.querySelector('select[name="colaborador_id[]"]');
            if (colaboradorSelect && colaboradorSelect.value !== '') {
                temAlgumaLinhaValida = true;
            }
        });
        if (!temAlgumaLinhaValida) {
            evento.preventDefault();
            alert('Adicione pelo menos uma linha com colaborador selecionado.');
        }
    });
})();
</script>