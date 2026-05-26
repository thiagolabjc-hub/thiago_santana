<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/master.php';

iniciarAdmin();

$provisionamentos = [];
$erroBanco = '';

try {
    $conexao = obterConexao();
    $resultado = $conexao->query(
        'SELECT pr.*, e.nome_empresa, e.slug, e.nome_banco
         FROM provisionamentos pr
         INNER JOIN empresas e ON e.id = pr.empresa_id
         ORDER BY pr.criado_em DESC
         LIMIT 300'
    );

    while ($linha = $resultado->fetch_assoc()) {
        $provisionamentos[] = $linha;
    }
} catch (Throwable $erro) {
    $erroBanco = 'Nao foi possivel carregar os provisionamentos. Importe database/update_codex_3.sql se ainda nao fez isso.';
}

renderAdminTopo('Provisionamentos');
?>
    <main class="admin-main">
        <div class="container">
            <div class="admin-heading">
                <div>
                    <span class="section-kicker">Ambientes</span>
                    <h1>Provisionamentos</h1>
                </div>
                <a class="btn btn-primary" href="empresas.php"><i class="fa-solid fa-building me-2"></i>Empresas</a>
            </div>

            <?php if ($erroBanco): ?>
                <div class="alert alert-warning" role="alert"><?= e($erroBanco); ?></div>
            <?php endif; ?>

            <section class="admin-panel">
                <div class="panel-header">
                    <div>
                        <h2>Historico de provisionamento</h2>
                        <p>Ultimos <?= e(count($provisionamentos)); ?> registro(s).</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Data/hora</th>
                                <th>Empresa</th>
                                <th>Banco</th>
                                <th>Status</th>
                                <th>Etapa</th>
                                <th>Mensagem</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$provisionamentos): ?>
                                <tr><td colspan="7" class="text-muted">Nenhum provisionamento registrado.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($provisionamentos as $item): ?>
                                <tr>
                                    <td><?= e(date('d/m/Y H:i', strtotime($item['criado_em']))); ?></td>
                                    <td>
                                        <strong><?= e($item['nome_empresa']); ?></strong><br>
                                        <small><?= e($item['slug']); ?></small>
                                    </td>
                                    <td><?= e($item['nome_banco']); ?></td>
                                    <td><?= badgeStatus((string) $item['status']); ?></td>
                                    <td><?= e($item['etapa']); ?></td>
                                    <td class="lead-message"><?= nl2br(e($item['mensagem'])); ?></td>
                                    <td><a class="btn btn-outline-primary btn-sm" href="ambiente_empresa.php?id=<?= e($item['empresa_id']); ?>"><i class="fa-solid fa-server me-1"></i>Ver</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
<?php renderAdminRodape(); ?>
