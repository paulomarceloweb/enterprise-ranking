<h1 class="h3 mb-1">Relatório Demográfico</h1>
<p class="text-muted mb-4">
    <?= $dados['total_ativos'] ?> colaborador(es) ativo(s) — <?= $dados['total_desligados'] ?> desligado(s) no total.
    <a href="/relatorios" class="ms-2">← Voltar aos relatórios</a>
</p>

<a href="?<?= htmlspecialchars(http_build_query(array_merge($_GET, ['exportar' => 1]))) ?>" class="btn btn-sm btn-outline-success mb-4">
    <i class="bi bi-file-earmark-excel"></i> Exportar Excel
</a>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h6">Por sexo</h2>
                <table class="table table-sm mb-0">
                    <?php foreach ($dados['por_sexo'] as $linha): ?>
                        <tr><td><?= htmlspecialchars(ucfirst($linha['sexo'])) ?></td><td class="text-end"><?= (int) $linha['total'] ?></td></tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h6">Por estado civil</h2>
                <table class="table table-sm mb-0">
                    <?php
                    $rotulosEstadoCivil = ['solteiro' => 'Solteiro(a)', 'casado' => 'Casado(a)', 'uniao_estavel' => 'União estável', 'divorciado' => 'Divorciado(a)', 'viuvo' => 'Viúvo(a)', 'nao_informado' => 'Não informado'];
                    foreach ($dados['por_estado_civil'] as $linha):
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($rotulosEstadoCivil[$linha['estado_civil']] ?? $linha['estado_civil']) ?></td>
                            <td class="text-end"><?= (int) $linha['total'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h6">Filhos</h2>
                <table class="table table-sm mb-0">
                    <?php foreach ($dados['por_filhos'] as $linha): ?>
                        <tr><td><?= htmlspecialchars($linha['grupo']) ?></td><td class="text-end"><?= (int) $linha['total'] ?></td></tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h6">Faixa etária</h2>
                <table class="table table-sm mb-0">
                    <?php foreach ($dados['por_faixa_etaria'] as $linha): ?>
                        <tr><td><?= htmlspecialchars($linha['faixa']) ?></td><td class="text-end"><?= (int) $linha['total'] ?></td></tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h6">Por departamento</h2>
                <table class="table table-sm mb-0">
                    <?php foreach ($dados['por_departamento'] as $linha): ?>
                        <tr><td><?= htmlspecialchars($linha['departamento'] ?? '—') ?></td><td class="text-end"><?= (int) $linha['total'] ?></td></tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h6">Por cidade</h2>
                <table class="table table-sm mb-0">
                    <?php foreach ($dados['por_cidade'] as $linha): ?>
                        <tr><td><?= htmlspecialchars($linha['cidade'] ?? '—') ?></td><td class="text-end"><?= (int) $linha['total'] ?></td></tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
</div>