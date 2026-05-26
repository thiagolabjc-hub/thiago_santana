<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/master.php';

iniciarAdmin();

$planos = [];
$erroBanco = '';

try {
    $conexao = obterConexao();
    $resultado = $conexao->query('SELECT * FROM planos ORDER BY status DESC, valor_mensal ASC, nome_plano ASC');

    while ($plano = $resultado->fetch_assoc()) {
        $planos[] = $plano;
    }
} catch (Throwable $erro) {
    $erroBanco = 'Nao foi possivel carregar os planos. Importe database/update_codex_2.sql se ainda nao fez isso.';
}

renderAdminTopo('Planos');
?>
    <main class="admin-main">
        <div class="container">
            <div class="admin-heading">
                <div>
                    <span class="section-kicker">Planos</span>
                    <h1>Ofertas comerciais</h1>
                </div>
                <a class="btn btn-primary" href="novo_plano.php"><i class="fa-solid fa-circle-plus me-2"></i>Novo Plano</a>
            </div>

            <?php if (!empty($_GET['sucesso'])): ?>
                <div class="alert alert-success" role="alert">Plano salvo com sucesso.</div>
            <?php endif; ?>

            <?php if ($erroBanco): ?>
                <div class="alert alert-warning" role="alert"><?= e($erroBanco); ?></div>
            <?php endif; ?>

            <section class="admin-panel">
                <div class="panel-header">
                    <div>
                        <h2>Planos cadastrados</h2>
                        <p><?= e(count($planos)); ?> registro(s) encontrado(s).</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Plano</th>
                                <th>Valor</th>
                                <th>Limites</th>
                                <th>Recursos</th>
                                <th>Status</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$planos): ?>
                                <tr><td colspan="6" class="text-muted">Nenhum plano cadastrado.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($planos as $plano): ?>
                                <tr>
                                    <td>
                                        <strong><?= e($plano['nome_plano']); ?></strong><br>
                                        <small><?= e($plano['descricao'] ?: 'Sem descricao.'); ?></small>
                                    </td>
                                    <td>R$ <?= e(number_format((float) $plano['valor_mensal'], 2, ',', '.')); ?></td>
                                    <td>
                                        <small>Usuarios: <?= e($plano['limite_usuarios'] ?? 'Ilimitado'); ?></small><br>
                                        <small>Chamados: <?= e($plano['limite_chamados'] ?? 'Ilimitado'); ?></small><br>
                                        <small>Pendencias: <?= e($plano['limite_pendencias'] ?? 'Ilimitado'); ?></small>
                                    </td>
                                    <td class="feature-badges">
                                        <?= (int) $plano['permite_relatorios'] === 1 ? '<span>Relatorios</span>' : ''; ?>
                                        <?= (int) $plano['permite_glosas'] === 1 ? '<span>Glosas</span>' : ''; ?>
                                        <?= (int) $plano['permite_portal_clinica'] === 1 ? '<span>Portal Clinica</span>' : ''; ?>
                                        <?= (int) $plano['permite_portal_motoboy'] === 1 ? '<span>Portal Motoboy</span>' : ''; ?>
                                    </td>
                                    <td><?= (int) $plano['status'] === 1 ? badgeStatus('ativo', 'plano') : badgeStatus('inativo', 'plano'); ?></td>
                                    <td class="table-actions">
                                        <a class="btn btn-primary btn-sm" href="editar_plano.php?id=<?= e($plano['id']); ?>"><i class="fa-solid fa-pen"></i></a>
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
