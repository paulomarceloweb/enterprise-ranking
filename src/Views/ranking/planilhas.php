<h1 class="h3 mb-1">Planilhas Enviadas</h1>
<p class="text-muted mb-4">Histórico de planilhas de ranking já processadas pelo sistema.</p>

<a href="/ranking/lote" class="btn btn-primary mb-3"><i class="bi bi-upload"></i> Enviar nova planilha</a>

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>Arquivo</th>
                <th>Mês/Ano</th>
                <th>Aba</th>
                <th>Entradas</th>
                <th>Gerados</th>
                <th>Enviado por</th>
                <th>Data</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($planilhas as $planilha): ?>
                <tr>
                    <td><i class="bi bi-file-earmark-spreadsheet text-success"></i> <?= htmlspecialchars($planilha['nome_original']) ?></td>
                    <td><?= (int) $planilha['mes'] ?>/<?= (int) $planilha['ano'] ?></td>
                    <td><?= htmlspecialchars($planilha['aba_processada']) ?></td>
                    <td><?= (int) $planilha['total_entradas'] ?></td>
                    <td>
                        <?= (int) $planilha['total_gerados'] ?>
                        <?php if ((int) $planilha['total_gerados'] < (int) $planilha['total_entradas']): ?>
                            <span class="badge bg-warning text-dark">com pendências</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($planilha['usuario_nome'] ?? '—') ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($planilha['criado_em'])) ?></td>
                    <td>
                        <a href="/ranking/planilhas/baixar?id=<?= (int) $planilha['id'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-download"></i> Baixar
                        </a>
                        <?php if (!empty($planilha['ranking_id'])): ?>
                            <a href="/ranking/detalhe?id=<?= (int) $planilha['ranking_id'] ?>" class="btn btn-sm btn-outline-secondary">
                                Ver ranking
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($planilhas)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted">Nenhuma planilha enviada ainda.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>