<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h1 class="h3 mb-1">Olá, <?= htmlspecialchars(\App\Core\Auth::nome()) ?></h1>
        <p class="text-muted mb-0">Aqui está o resumo do sistema hoje, <?= date('d/m/Y') ?>.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 text-muted small mb-1">
                    <i class="bi bi-people-fill"></i> Colaboradores ativos
                </div>
                <div class="h2 mb-0"><?= (int) $demografico['total_ativos'] ?></div>
                <a href="/colaboradores?status=ativo" class="small">Ver todos →</a>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 text-muted small mb-1">
                    <i class="bi bi-person-dash-fill"></i> Desligados
                </div>
                <div class="h2 mb-0"><?= (int) $demografico['total_desligados'] ?></div>
                <a href="/colaboradores?status=desligado" class="small">Ver todos →</a>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 text-muted small mb-1">
                    <i class="bi bi-balloon-fill"></i> Aniversariantes (30d)
                </div>
                <div class="h2 mb-0"><?= count($aniversariantesMes) ?></div>
                <a href="/relatorios/aniversariantes" class="small">Ver relatório →</a>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 text-muted small mb-1">
                    <i class="bi bi-trophy-fill"></i> Ranking de <?= date('m/Y') ?>
                </div>
                <div class="h2 mb-0"><?= $rankingAtual ? (int) $rankingAtual['total_artes'] : 0 ?></div>
                <?php if ($rankingAtual): ?>
                    <a href="/ranking/detalhe?id=<?= (int) $rankingAtual['id'] ?>" class="small">Ver artes →</a>
                <?php else: ?>
                    <a href="/ranking/novo" class="small">Gerar agora →</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h2 class="h6 mb-3">Atalhos rápidos</h2>
        <div class="d-flex flex-wrap gap-2">
            <a href="/colaboradores/novo" class="btn btn-sm btn-outline-primary"><i class="bi bi-person-plus"></i> Novo colaborador</a>
            <a href="/ranking/novo" class="btn btn-sm btn-outline-primary"><i class="bi bi-trophy"></i> Gerar ranking</a>
            <a href="/aniversariantes/novo" class="btn btn-sm btn-outline-primary"><i class="bi bi-balloon"></i> Aniversariantes</a>
            <a href="/relatorios" class="btn btn-sm btn-outline-primary"><i class="bi bi-bar-chart"></i> Relatórios</a>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6">Turnover — últimos 12 meses</h2>
                <canvas id="graficoTurnover" height="220"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6">Colaboradores por departamento</h2>
                <canvas id="graficoDepartamentos" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6">Top do ranking em <?= date('Y') ?></h2>
                <ul class="list-group list-group-flush">
                    <?php foreach ($topRankingAno as $indice => $linha): ?>
                        <li class="list-group-item small d-flex justify-content-between px-0">
                            <span><?= $indice + 1 ?>º <?= htmlspecialchars($linha['colaborador_nome'] ?? '—') ?></span>
                            <span class="text-muted">🥇<?= (int) $linha['primeiros'] ?> 🥈<?= (int) $linha['segundos'] ?> 🥉<?= (int) $linha['terceiros'] ?></span>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($topRankingAno)): ?>
                        <li class="list-group-item small text-muted px-0">Nenhuma arte de ranking gerada neste ano ainda.</li>
                    <?php endif; ?>
                </ul>
                <a href="/relatorios/ranking" class="small d-inline-block mt-2">Ver relatório completo →</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6">Atividade recente</h2>
                <ul class="list-group list-group-flush">
                    <?php
                    $rotulosTipo = ['ranking' => 'Ranking', 'boas_vindas' => 'Boas-vindas', 'promocao' => 'Promoção', 'aniversario' => 'Aniversário'];
                    foreach (array_slice($artesRecentes, 0, 6) as $arte):
                    ?>
                        <li class="list-group-item small d-flex justify-content-between px-0">
                            <span>
                                <strong><?= htmlspecialchars($rotulosTipo[$arte['tipo']] ?? $arte['tipo']) ?></strong>
                                <?php if (!empty($arte['colaborador_nome'])): ?> — <?= htmlspecialchars($arte['colaborador_nome']) ?><?php endif; ?>
                            </span>
                            <span class="text-muted"><?= !empty($arte['gerado_em']) ? date('d/m', strtotime($arte['gerado_em'])) : '' ?></span>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($artesRecentes)): ?>
                        <li class="list-group-item small text-muted px-0">Nenhuma arte gerada ainda.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6">Ocorrências recentes</h2>
                <ul class="list-group list-group-flush">
                    <?php foreach (array_slice($ocorrenciasRecentes, 0, 6) as $ocorrencia): ?>
                        <li class="list-group-item small d-flex justify-content-between px-0">
                            <span>
                                <strong><?= htmlspecialchars($ocorrencia['tipo_nome'] ?? 'Ocorrência') ?></strong>
                                — <?= htmlspecialchars($ocorrencia['colaborador_nome'] ?? '—') ?>
                            </span>
                            <span class="text-muted"><?= date('d/m', strtotime($ocorrencia['data_evento'])) ?></span>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($ocorrenciasRecentes)): ?>
                        <li class="list-group-item small text-muted px-0">Nenhuma ocorrência registrada ainda.</li>
                    <?php endif; ?>
                </ul>
                <a href="/relatorios/ocorrencias" class="small d-inline-block mt-2">Ver histórico completo →</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function () {
    var corPrimaria = getComputedStyle(document.documentElement).getPropertyValue('--cor-primaria').trim() || '#f97316';

    var dadosTurnover = <?= json_encode($turnover12Meses) ?>;
    new Chart(document.getElementById('graficoTurnover'), {
        type: 'bar',
        data: {
            labels: dadosTurnover.map(function (m) { return m.rotulo; }),
            datasets: [
                {
                    label: 'Admissões',
                    data: dadosTurnover.map(function (m) { return m.admissoes; }),
                    backgroundColor: corPrimaria,
                },
                {
                    label: 'Desligamentos',
                    data: dadosTurnover.map(function (m) { return m.desligamentos; }),
                    backgroundColor: '#6c757d',
                },
            ],
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
        },
    });

    var dadosDepartamento = <?= json_encode($porDepartamento) ?>;
    new Chart(document.getElementById('graficoDepartamentos'), {
        type: 'doughnut',
        data: {
            labels: dadosDepartamento.map(function (d) { return d.departamento || 'Sem departamento'; }),
            datasets: [{
                data: dadosDepartamento.map(function (d) { return d.total; }),
                backgroundColor: ['#f97316', '#0d6efd', '#198754', '#6f42c1', '#dc3545', '#20c997', '#fd7e14', '#6c757d'],
            }],
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
        },
    });
})();
</script>