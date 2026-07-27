<h1 class="h3 mb-1">Bem-vindo, <?= htmlspecialchars(\App\Core\Auth::nome()) ?>!</h1>
<p class="text-muted mb-4">Nível de acesso: <?= htmlspecialchars(\App\Core\Auth::nivel()) ?></p>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Colaboradores ativos</div>
                <div class="h3 mb-0"><?= (int) $demografico['total_ativos'] ?></div>
                <a href="/colaboradores?status=ativo" class="small">Ver todos</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Desligados</div>
                <div class="h3 mb-0"><?= (int) $demografico['total_desligados'] ?></div>
                <a href="/colaboradores?status=desligado" class="small">Ver todos</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Aniversariantes (30 dias)</div>
                <div class="h3 mb-0"><?= count($aniversariantesMes) ?></div>
                <a href="/relatorios/aniversariantes" class="small">Ver relatório</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Ranking de <?= date('m/Y') ?></div>
                <div class="h3 mb-0"><?= $rankingAtual ? (int) $rankingAtual['total_artes'] : 0 ?></div>
                <?php if ($rankingAtual): ?>
                    <a href="/ranking/detalhe?id=<?= (int) $rankingAtual['id'] ?>" class="small">Ver artes</a>
                <?php else: ?>
                    <a href="/ranking/novo" class="small">Gerar agora</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h2 class="h6">Atalhos rápidos</h2>
                <div class="d-flex flex-wrap gap-2">
                    <a href="/colaboradores/novo" class="btn btn-sm btn-outline-primary"><i class="bi bi-person-plus"></i> Novo colaborador</a>
                    <a href="/ranking/novo" class="btn btn-sm btn-outline-primary"><i class="bi bi-trophy"></i> Gerar ranking</a>
                    <a href="/aniversariantes/novo" class="btn btn-sm btn-outline-primary"><i class="bi bi-balloon"></i> Aniversariantes</a>
                    <a href="/relatorios" class="btn btn-sm btn-outline-primary"><i class="bi bi-bar-chart"></i> Relatórios</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h6">Atividade recente</h2>
                <ul class="list-group list-group-flush">
                    <?php
                    $rotulosTipo = ['ranking' => 'Ranking', 'boas_vindas' => 'Boas-vindas', 'promocao' => 'Promoção', 'aniversario' => 'Aniversário'];
                    foreach ($artesRecentes as $arte):
                    ?>
                        <li class="list-group-item small d-flex justify-content-between">
                            <span>
                                <strong><?= htmlspecialchars($rotulosTipo[$arte['tipo']] ?? $arte['tipo']) ?></strong>
                                <?php if (!empty($arte['colaborador_nome'])): ?> — <?= htmlspecialchars($arte['colaborador_nome']) ?><?php endif; ?>
                            </span>
                            <span class="text-muted"><?= !empty($arte['gerado_em']) ? date('d/m H:i', strtotime($arte['gerado_em'])) : '' ?></span>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($artesRecentes)): ?>
                        <li class="list-group-item small text-muted">Nenhuma arte gerada ainda.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h6">Ocorrências recentes</h2>
                <ul class="list-group list-group-flush">
                    <?php foreach ($ocorrenciasRecentes as $ocorrencia): ?>
                        <li class="list-group-item small d-flex justify-content-between">
                            <span>
                                <strong><?= htmlspecialchars($ocorrencia['tipo_nome'] ?? 'Ocorrência') ?></strong>
                                — <?= htmlspecialchars($ocorrencia['colaborador_nome'] ?? '—') ?>
                            </span>
                            <span class="text-muted"><?= date('d/m/Y', strtotime($ocorrencia['data_evento'])) ?></span>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($ocorrenciasRecentes)): ?>
                        <li class="list-group-item small text-muted">Nenhuma ocorrência registrada ainda.</li>
                    <?php endif; ?>
                </ul>
                <a href="/relatorios/ocorrencias" class="small d-inline-block mt-2">Ver histórico completo</a>
            </div>
        </div>
    </div>
</div>