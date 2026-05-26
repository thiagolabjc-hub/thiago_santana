<?php
$statusEmpresas = ['ATIVA', 'EM_IMPLANTACAO', 'SUSPENSA', 'CANCELADA'];
?>
<form class="admin-panel config-form" method="post" action="salvar_empresa.php" novalidate>
    <input type="hidden" name="id" value="<?= e($empresa['id'] ?? 0); ?>">

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="form-section">
                <h2>Dados comerciais</h2>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label" for="nome_empresa">Nome da empresa</label>
                        <input class="form-control" id="nome_empresa" name="nome_empresa" value="<?= e($empresa['nome_empresa'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="slug">Slug</label>
                        <input class="form-control" id="slug" name="slug" value="<?= e($empresa['slug'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="nome_fantasia">Nome fantasia</label>
                        <input class="form-control" id="nome_fantasia" name="nome_fantasia" value="<?= e($empresa['nome_fantasia'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="cnpj">CNPJ</label>
                        <input class="form-control" id="cnpj" name="cnpj" value="<?= e($empresa['cnpj'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="responsavel">Responsavel</label>
                        <input class="form-control" id="responsavel" name="responsavel" value="<?= e($empresa['responsavel'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email_responsavel">E-mail responsavel</label>
                        <input class="form-control" id="email_responsavel" name="email_responsavel" type="email" value="<?= e($empresa['email_responsavel'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="telefone_responsavel">Telefone responsavel</label>
                        <input class="form-control" id="telefone_responsavel" name="telefone_responsavel" value="<?= e($empresa['telefone_responsavel'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="subdominio">Subdominio</label>
                        <input class="form-control" id="subdominio" name="subdominio" value="<?= e($empresa['subdominio'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2>Dados para banco futuro</h2>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="nome_banco">Nome do banco</label>
                        <input class="form-control" id="nome_banco" name="nome_banco" value="<?= e($empresa['nome_banco'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="usuario_banco">Usuario do banco</label>
                        <input class="form-control" id="usuario_banco" name="usuario_banco" value="<?= e($empresa['usuario_banco'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="senha_banco">Senha do banco</label>
                        <input class="form-control" id="senha_banco" name="senha_banco" type="password" autocomplete="new-password">
                        <?php if (!empty($empresa['senha_banco'])): ?>
                            <small class="text-muted">Deixe em branco para manter a senha atual.</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2>Observacoes</h2>
                <textarea class="form-control" name="observacoes" rows="5"><?= e($empresa['observacoes'] ?? ''); ?></textarea>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="form-section sticky-side">
                <h2>Status e contrato</h2>
                <div class="mb-3">
                    <label class="form-label" for="plano_id">Plano</label>
                    <select class="form-select" id="plano_id" name="plano_id">
                        <option value="">Sem plano</option>
                        <?php foreach ($planos as $plano): ?>
                            <option value="<?= e($plano['id']); ?>" <?= (string) ($empresa['plano_id'] ?? '') === (string) $plano['id'] ? 'selected' : ''; ?>>
                                <?= e($plano['nome_plano']); ?><?= (int) $plano['status'] === 0 ? ' (inativo)' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                        <?php foreach ($statusEmpresas as $statusItem): ?>
                            <option value="<?= e($statusItem); ?>" <?= ($empresa['status'] ?? '') === $statusItem ? 'selected' : ''; ?>><?= e($statusItem); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="data_inicio">Data inicio</label>
                    <input class="form-control" id="data_inicio" name="data_inicio" type="date" value="<?= e($empresa['data_inicio'] ?? ''); ?>">
                </div>
                <div class="mb-4">
                    <label class="form-label" for="data_expiracao">Data expiracao</label>
                    <input class="form-control" id="data_expiracao" name="data_expiracao" type="date" value="<?= e($empresa['data_expiracao'] ?? ''); ?>">
                </div>
                <button class="btn btn-primary w-100" type="submit">
                    <i class="fa-solid fa-floppy-disk me-2"></i>Salvar Empresa
                </button>
            </div>
        </div>
    </div>
</form>
