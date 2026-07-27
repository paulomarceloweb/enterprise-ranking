<h1 class="h3 mb-4">Editar Colaborador</h1>

<div class="row">
<div class="col-md-8">
<form method="POST" action="/colaboradores/atualizar" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $colaborador['id'] ?>">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Nome *</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($colaborador['nome']) ?>" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Foto (deixe em branco pra manter a atual)</label>
            <input type="file" name="foto" accept=".jpg,.jpeg,.png" class="form-control">
            <img src="/<?= htmlspecialchars($colaborador['foto']) ?>" style="width:60px;height:60px;object-fit:cover;border-radius:8px;" class="mt-2">
        </div>
        <div class="col-md-4">
            <label class="form-label">Sexo *</label>
            <select name="sexo" class="form-select" required>
                <option value="masculino" <?= $colaborador['sexo'] === 'masculino' ? 'selected' : '' ?>>Masculino</option>
                <option value="feminino" <?= $colaborador['sexo'] === 'feminino' ? 'selected' : '' ?>>Feminino</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Data de nascimento *</label>
            <input type="date" name="data_nascimento" value="<?= $colaborador['data_nascimento'] ?>" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Data de admissão *</label>
            <input type="date" name="data_admissao" value="<?= $colaborador['data_admissao'] ?>" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Status *</label>
            <select name="status" id="statusColaborador" class="form-select" required onchange="document.getElementById('campoDataDesligamento').style.display = this.value === 'desligado' ? 'block' : 'none';">
                <option value="ativo" <?= $colaborador['status'] === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                <option value="desligado" <?= $colaborador['status'] === 'desligado' ? 'selected' : '' ?>>Desligado</option>
            </select>
        </div>
        <div class="col-md-4" id="campoDataDesligamento" style="display: <?= $colaborador['status'] === 'desligado' ? 'block' : 'none' ?>;">
            <label class="form-label">Data de desligamento</label>
            <input type="date" name="data_desligamento" value="<?= htmlspecialchars($colaborador['data_desligamento'] ?? '') ?>" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label">Cidade *</label>
            <select name="cidade_id" class="form-select" required>
                <option value="">Selecione</option>
                <?php foreach ($cidades as $cidade): ?>
                    <option value="<?= $cidade['id'] ?>" <?= $cidade['id'] == $colaborador['cidade_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cidade['nome']) ?>/<?= htmlspecialchars($cidade['uf']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Departamento *</label>
            <select name="departamento_id" class="form-select" required>
                <option value="">Selecione</option>
                <?php foreach ($departamentos as $departamento): ?>
                    <option value="<?= $departamento['id'] ?>" <?= $departamento['id'] == $colaborador['departamento_id'] ? 'selected' : '' ?>><?= htmlspecialchars($departamento['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Cargo *</label>
            <input type="text" name="cargo" value="<?= htmlspecialchars($colaborador['cargo']) ?>" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Telefone</label>
            <input type="text" name="telefone" value="<?= htmlspecialchars($colaborador['telefone'] ?? '') ?>" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label">E-mail</label>
            <input type="email" name="email" value="<?= htmlspecialchars($colaborador['email'] ?? '') ?>" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Instagram</label>
            <input type="text" name="instagram" value="<?= htmlspecialchars($colaborador['instagram'] ?? '') ?>" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Facebook</label>
            <input type="text" name="facebook" value="<?= htmlspecialchars($colaborador['facebook'] ?? '') ?>" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label">Estado civil</label>
            <select name="estado_civil" class="form-select">
                <option value="" <?= empty($colaborador['estado_civil']) ? 'selected' : '' ?>>Não informado</option>
                <option value="solteiro" <?= $colaborador['estado_civil'] === 'solteiro' ? 'selected' : '' ?>>Solteiro(a)</option>
                <option value="casado" <?= $colaborador['estado_civil'] === 'casado' ? 'selected' : '' ?>>Casado(a)</option>
                <option value="uniao_estavel" <?= $colaborador['estado_civil'] === 'uniao_estavel' ? 'selected' : '' ?>>União estável</option>
                <option value="divorciado" <?= $colaborador['estado_civil'] === 'divorciado' ? 'selected' : '' ?>>Divorciado(a)</option>
                <option value="viuvo" <?= $colaborador['estado_civil'] === 'viuvo' ? 'selected' : '' ?>>Viúvo(a)</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Quantidade de filhos</label>
            <input type="number" name="quantidade_filhos" min="0" max="20" value="<?= htmlspecialchars($colaborador['quantidade_filhos'] ?? '') ?>" class="form-control" placeholder="Deixe em branco se não informado">
        </div>
        <div class="col-12">
            <label class="form-label">Observações</label>
            <textarea name="observacoes" class="form-control" rows="3"><?= htmlspecialchars($colaborador['observacoes'] ?? '') ?></textarea>
        </div>
    </div>
    <div class="mt-4">
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="/colaboradores" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
</div>
<div class="col-md-4">
    <p class="text-muted small mb-3">
        Cadastrado no sistema em
        <?= !empty($colaborador['criado_em']) ? date('d/m/Y \à\s H:i', strtotime($colaborador['criado_em'])) : '—' ?>
    </p>
    <h2 class="h6">Histórico</h2>
    <ul class="list-group">
        <?php foreach ($historico as $evento): ?>
            <li class="list-group-item small">
                <strong><?= htmlspecialchars($evento['tipo']) ?></strong> — <?= htmlspecialchars($evento['data_evento']) ?><br>
                <?php if ($evento['detalhes']): ?><?= htmlspecialchars($evento['detalhes']) ?><?php endif; ?>
            </li>
        <?php endforeach; ?>
        <?php if (empty($historico)): ?>
            <li class="list-group-item small text-muted">Nenhum evento registrado.</li>
        <?php endif; ?>
    </ul>
    <form method="POST" action="/boas-vindas/gerar" class="mt-3">
        <input type="hidden" name="colaborador_id" value="<?= $colaborador['id'] ?>">
        <button type="submit" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-stars"></i> Gerar arte de Boas-vindas
        </button>
    </form>

    <a href="/promocoes/nova?colaborador_id=<?= $colaborador['id'] ?>" class="btn btn-outline-primary btn-sm mt-2">
        <i class="bi bi-arrow-up-circle"></i> Registrar Promoção
    </a>

    <hr class="my-4">

    <h2 class="h6">Ocorrências</h2>
    <a href="/ocorrencias/nova?colaborador_id=<?= $colaborador['id'] ?>" class="btn btn-outline-primary btn-sm mb-2">
        <i class="bi bi-file-earmark-plus"></i> Registrar ocorrência
    </a>
    <ul class="list-group mb-3">
        <?php foreach ($ocorrencias as $ocorrencia): ?>
            <li class="list-group-item small">
                <strong><?= htmlspecialchars($ocorrencia['tipo_nome'] ?? 'Ocorrência') ?></strong>
                — <?= date('d/m/Y', strtotime($ocorrencia['data_evento'])) ?><br>
                <?php if (!empty($ocorrencia['descricao'])): ?>
                    <span class="text-muted"><?= nl2br(htmlspecialchars($ocorrencia['descricao'])) ?></span><br>
                <?php endif; ?>
                <div class="mt-1 d-flex gap-2 align-items-center">
                    <?php if (!empty($ocorrencia['arquivo'])): ?>
                        <a href="/ocorrencias/anexo?id=<?= (int) $ocorrencia['id'] ?>" class="btn btn-sm btn-outline-secondary py-0">
                            <i class="bi bi-paperclip"></i> Anexo
                        </a>
                    <?php endif; ?>
                    <form method="POST" action="/ocorrencias/excluir" onsubmit="return confirm('Excluir esta ocorrência?');">
                        <input type="hidden" name="id" value="<?= (int) $ocorrencia['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-link text-danger p-0">excluir</button>
                    </form>
                </div>
            </li>
        <?php endforeach; ?>
        <?php if (empty($ocorrencias)): ?>
            <li class="list-group-item small text-muted">Nenhuma ocorrência registrada.</li>
        <?php endif; ?>
    </ul>

    <form method="POST" action="/colaboradores/excluir" class="mt-3" onsubmit="return confirm('Excluir este colaborador definitivamente?');">
        <input type="hidden" name="id" value="<?= $colaborador['id'] ?>">
        <button type="submit" class="btn btn-outline-danger btn-sm">Excluir colaborador</button>
    </form>
</div>
</div>