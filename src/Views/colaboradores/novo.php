<h1 class="h3 mb-4">Novo Colaborador</h1>

<?php if (!empty($_SESSION['erro_colaborador'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['erro_colaborador']) ?></div>
    <?php unset($_SESSION['erro_colaborador']); ?>
<?php endif; ?>

<form method="POST" action="/colaboradores" enctype="multipart/form-data" style="max-width: 700px;">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Nome *</label>
            <input type="text" name="nome" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Foto *</label>
            <input type="file" name="foto" accept=".jpg,.jpeg,.png" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Sexo *</label>
            <select name="sexo" class="form-select" required>
                <option value="masculino">Masculino</option>
                <option value="feminino">Feminino</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Data de nascimento *</label>
            <input type="date" name="data_nascimento" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Data de admissão *</label>
            <input type="date" name="data_admissao" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Cidade *</label>
            <select name="cidade_id" class="form-select" required>
                <option value="">Selecione</option>
                <?php foreach ($cidades as $cidade): ?>
                    <option value="<?= $cidade['id'] ?>"><?= htmlspecialchars($cidade['nome']) ?>/<?= htmlspecialchars($cidade['uf']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Departamento *</label>
            <select name="departamento_id" class="form-select" required>
                <option value="">Selecione</option>
                <?php foreach ($departamentos as $departamento): ?>
                    <option value="<?= $departamento['id'] ?>"><?= htmlspecialchars($departamento['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Cargo *</label>
            <input type="text" name="cargo" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Telefone</label>
            <input type="text" name="telefone" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label">E-mail</label>
            <input type="email" name="email" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Instagram</label>
            <input type="text" name="instagram" class="form-control" placeholder="@usuario">
        </div>
        <div class="col-md-3">
            <label class="form-label">Facebook</label>
            <input type="text" name="facebook" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label">Estado civil</label>
            <select name="estado_civil" class="form-select">
                <option value="">Não informado</option>
                <option value="solteiro">Solteiro(a)</option>
                <option value="casado">Casado(a)</option>
                <option value="uniao_estavel">União estável</option>
                <option value="divorciado">Divorciado(a)</option>
                <option value="viuvo">Viúvo(a)</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Quantidade de filhos</label>
            <input type="number" name="quantidade_filhos" min="0" max="20" class="form-control" placeholder="Deixe em branco se não informado">
        </div>
        <div class="col-12">
            <label class="form-label">Observações</label>
            <textarea name="observacoes" class="form-control" rows="3"></textarea>
        </div>
    </div>
    <div class="mt-4">
        <button type="submit" class="btn btn-primary">Cadastrar</button>
        <a href="/colaboradores" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>