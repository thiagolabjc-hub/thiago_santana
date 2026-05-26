<?php
$statusContratos = ['ATIVO', 'SUSPENSO', 'CANCELADO', 'EXPIRADO'];
?>
<form class="admin-panel config-form" method="post" action="salvar_contrato.php" novalidate>
    <input type="hidden" name="id" value="<?= e($contrato['id'] ?? 0); ?>">

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="form-section">
                <h2>Contrato</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="empresa_id">Empresa</label>
                        <select class="form-select" id="empresa_id" name="empresa_id" required>
                            <option value="">Selecione</option>
                            <?php foreach ($empresas as $empresa): ?>
                                <option value="<?= e($empresa['id']); ?>" <?= (string) ($contrato['empresa_id'] ?? '') === (string) $empresa['id'] ? 'selected' : ''; ?>>
                                    <?= e($empresa['nome_empresa']); ?> (<?= e($empresa['status']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="plano_id">Plano</label>
                        <select class="form-select" id="plano_id" name="plano_id" required>
                            <option value="">Selecione</option>
                            <?php foreach ($planos as $plano): ?>
                                <option value="<?= e($plano['id']); ?>" <?= (string) ($contrato['plano_id'] ?? '') === (string) $plano['id'] ? 'selected' : ''; ?>>
                                    <?= e($plano['nome_plano']); ?><?= (int) $plano['status'] === 0 ? ' (inativo)' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="data_inicio">Data inicio</label>
                        <input class="form-control" id="data_inicio" name="data_inicio" type="date" value="<?= e($contrato['data_inicio'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="data_fim">Data fim</label>
                        <input class="form-control" id="data_fim" name="data_fim" type="date" value="<?= e($contrato['data_fim'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="valor_contratado">Valor contratado</label>
                        <input class="form-control" id="valor_contratado" name="valor_contratado" type="number" min="0" step="0.01" value="<?= e(number_format((float) ($contrato['valor_contratado'] ?? 0), 2, '.', '')); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="forma_pagamento">Forma de pagamento</label>
                        <input class="form-control" id="forma_pagamento" name="forma_pagamento" value="<?= e($contrato['forma_pagamento'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-select" id="status" name="status">
                            <?php foreach ($statusContratos as $statusItem): ?>
                                <option value="<?= e($statusItem); ?>" <?= ($contrato['status'] ?? '') === $statusItem ? 'selected' : ''; ?>><?= e($statusItem); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="observacoes">Observacoes</label>
                        <textarea class="form-control" id="observacoes" name="observacoes" rows="5"><?= e($contrato['observacoes'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="form-section sticky-side">
                <h2>Resumo</h2>
                <p class="section-text">O contrato registra apenas a camada comercial do ELOS. Nenhum banco de cliente sera criado automaticamente nesta etapa.</p>
                <button class="btn btn-primary w-100" type="submit">
                    <i class="fa-solid fa-floppy-disk me-2"></i>Salvar Contrato
                </button>
            </div>
        </div>
    </div>
</form>
