<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/master.php';

iniciarAdmin();

$logs = [];
$erroBanco = '';

try {
    $conexao = obterConexao();
    $resultado = $conexao->query(
        'SELECT l.*, u.nome AS usuario_nome, u.email AS usuario_email
         FROM logs_master l
         LEFT JOIN usuarios_admin u ON u.id = l.usuario_admin_id
         ORDER BY l.criado_em DESC
         LIMIT 300'
    );

    while ($log = $resultado->fetch_assoc()) {
        $logs[] = $log;
    }
} catch (Throwable $erro) {
    $erroBanco = 'Nao foi possivel carregar os logs. Importe database/update_codex_2.sql se ainda nao fez isso.';
}

renderAdminTopo('Logs Master');
?>
    <main class="admin-main">
        <div class="container">
            <div class="admin-heading">
                <div>
                    <span class="section-kicker">Auditoria</span>
                    <h1>Logs Master</h1>
                </div>
            </div>

            <?php if ($erroBanco): ?>
                <div class="alert alert-warning" role="alert"><?= e($erroBanco); ?></div>
            <?php endif; ?>

            <section class="admin-panel">
                <div class="panel-header">
                    <div>
                        <h2>Eventos registrados</h2>
                        <p>Ultimos <?= e(count($logs)); ?> evento(s) comerciais.</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Data/hora</th>
                                <th>Usuario</th>
                                <th>Acao</th>
                                <th>Entidade</th>
                                <th>Detalhes</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$logs): ?>
                                <tr><td colspan="6" class="text-muted">Nenhum log registrado ainda.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= e(date('d/m/Y H:i', strtotime($log['criado_em']))); ?></td>
                                    <td>
                                        <?= e($log['usuario_nome'] ?: 'Sistema'); ?><br>
                                        <small><?= e($log['usuario_email'] ?: ''); ?></small>
                                    </td>
                                    <td><strong><?= e($log['acao']); ?></strong></td>
                                    <td>
                                        <?= e($log['entidade'] ?: '-'); ?><br>
                                        <small><?= $log['entidade_id'] ? '#' . e($log['entidade_id']) : ''; ?></small>
                                    </td>
                                    <td class="lead-message"><?= nl2br(e($log['detalhes'] ?: '-')); ?></td>
                                    <td><?= e($log['ip'] ?: '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
<?php renderAdminRodape(); ?>
