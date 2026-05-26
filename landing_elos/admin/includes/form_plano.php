<form class="admin-panel config-form" method="post" action="salvar_plano.php" novalidate>
    <input type="hidden" name="id" value="<?= e($plano['id'] ?? 0); ?>">

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="form-section">
                <h2>Dados do plano</h2>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label" for="nome_plano">Nome do plano</label>
                        <input class="form-control" id="nome_plano" name="nome_plano" value="<?= e($plano['nome_plano'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="valor_mensal">Valor mensal</label>
                        <input class="form-control" id="valor_mensal" name="valor_mensal" type="number" min="0" step="0.01" value="<?= e(number_format((float) ($plano['valor_mensal'] ?? 0), 2, '.', '')); ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="descricao">Descricao</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="4"><?= e($plano['descricao'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2>Limites</h2>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="limite_usuarios">Limite de usuarios</label>
                        <input class="form-control" id="limite_usuarios" name="limite_usuarios" type="number" min="0" value="<?= e($plano['limite_usuarios'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="limite_chamados">Limite de chamados</label>
                        <input class="form-control" id="limite_chamados" name="limite_chamados" type="number" min="0" value="<?= e($plano['limite_chamados'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="limite_pendencias">Limite de pendencias</label>
                        <input class="form-control" id="limite_pendencias" name="limite_pendencias" type="number" min="0" value="<?= e($plano['limite_pendencias'] ?? ''); ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="form-section sticky-side">
                <h2>Recursos e status</h2>
                <div class="permission-list">
                    <label><input type="checkbox" name="permite_portal_clinica" value="1" <?= (int) ($plano['permite_portal_clinica'] ?? 0) === 1 ? 'checked' : ''; ?>> Portal da Clinica</label>
                    <label><input type="checkbox" name="permite_portal_motoboy" value="1" <?= (int) ($plano['permite_portal_motoboy'] ?? 0) === 1 ? 'checked' : ''; ?>> Portal do Motoboy</label>
                    <label><input type="checkbox" name="permite_glosas" value="1" <?= (int) ($plano['permite_glosas'] ?? 0) === 1 ? 'checked' : ''; ?>> Glosas</label>
                    <label><input type="checkbox" name="permite_relatorios" value="1" <?= (int) ($plano['permite_relatorios'] ?? 1) === 1 ? 'checked' : ''; ?>> Relatorios</label>
                </div>
                <div class="mt-4 mb-4">
                    <label class="form-label" for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="1" <?= (int) ($plano['status'] ?? 1) === 1 ? 'selected' : ''; ?>>Ativo</option>
                        <option value="0" <?= (int) ($plano['status'] ?? 1) === 0 ? 'selected' : ''; ?>>Inativo</option>
                    </select>
                </div>
                <button class="btn btn-primary w-100" type="submit">
                    <i class="fa-solid fa-floppy-disk me-2"></i>Salvar Plano
                </button>
            </div>
        </div>
    </div>
</form>
