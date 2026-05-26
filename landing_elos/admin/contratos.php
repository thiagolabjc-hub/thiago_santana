<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/master.php';

iniciarAdmin();

$contratos = [];
$erroBanco = '';

try {
    $conexao = obterConexao();
    $resultado = $conexao->query(
        'SELECT c.*, e.nome_empresa, e.slug, p.nome_plano
         FROM contratos c
         INNER JOIN empresas e ON e.id = c.empresa_id
         INNER JOIN planos p ON p.id = c.plano_id
         ORDER BY c.criado_em DESC'
    );

    while ($contrato = $resultado->fetch_assoc()) {
        $contratos[] = $contrato;
    }
} catch (Throwable $erro) {
    $erroBanco = 'Nao foi possivel carregar os contratos. Importe database/update_codex_2.sql se ainda nao fez isso.';
}

renderAdminTopo('Contratos');
?>
    <main class="admin-main">
        <div class="container">
            <div class="admin-heading">
                <div>
                    <span class="section-kicker">Contratos</span>
                    <h1>Contratos comerciais</h1>
                </div>
                <a class="btn btn-primary" href="novo_contrato.php"><i class="fa-solid fa-file-circle-plus me-2"></i>Novo Contrato</a>
            </div>

            <?php if (!empty($_GET['sucesso'])): ?>
                <div class="alert alert-success" role="alert">Contrato salvo com sucesso.</div>
            <?php endif; ?>

            <?php if ($erroBanco): ?>
                <div class="alert alert-warning" role="alert"><?= e($erroBanco); ?></div>
            <?php endif; ?>

            <section class="admin-panel">
                <div class="panel-header">
                    <div>
                        <h2>Contratos cadastrados</h2>
                        <p><?= e(count($contratos)); ?> registro(s) encontrado(s).</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Empresa</th>
                                <th>Plano</th>
                                <th>Periodo</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th>Pagamento</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$contratos): ?>
                                <tr><td colspan="7" class="text-muted">Nenhum contrato cadastrado.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($contratos as $contrato): ?>
                                <tr>
                                    <td>
                                        <strong><?= e($contrato['nome_empresa']); ?></strong><br>
                                        <small><?= e($contrato['slug']); ?></small>
                                    </td>
                                    <td><?= e($contrato['nome_plano']); ?></td>
                                    <td>
                                        <?= e(date('d/m/Y', strtotime($contrato['data_inicio']))); ?><br>
                                        <small><?= $contrato['data_fim'] ? e(date('d/m/Y', strtotime($contrato['data_fim']))) : 'Sem fim definido'; ?></small>
                                    </td>
                                    <td>R$ <?= e(number_format((float) $contrato['valor_contratado'], 2, ',', '.')); ?></td>
                                    <td><?= badgeStatus((string) $contrato['status']); ?></td>
                                    <td><?= e($contrato['forma_pagamento'] ?: 'Nao informado'); ?></td>
                                    <td class="table-actions">
                                        <a class="btn btn-primary btn-sm" href="editar_contrato.php?id=<?= e($contrato['id']); ?>"><i class="fa-solid fa-pen"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
<?php renderAdminRodape(); ?>
