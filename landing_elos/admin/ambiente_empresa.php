<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/master.php';

iniciarAdmin();

$id = (int) ($_GET['id'] ?? 0);
$empresa = null;
$provisionamentos = [];
$erroBanco = '';

try {
    $conexao = obterConexao();
    $stmt = $conexao->prepare(
        'SELECT e.*, p.nome_plano
         FROM empresas e
         LEFT JOIN planos p ON p.id = e.plano_id
         WHERE e.id = ?
         LIMIT 1'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $empresa = $stmt->get_result()->fetch_assoc();

    if ($empresa) {
        $stmtProv = $conexao->prepare(
            'SELECT status, etapa, mensagem, criado_em, atualizado_em
             FROM provisionamentos
             WHERE empresa_id = ?
             ORDER BY criado_em DESC
             LIMIT 20'
        );
        $stmtProv->bind_param('i', $id);
        $stmtProv->execute();
        $resultadoProv = $stmtProv->get_result();

        while ($linha = $resultadoProv->fetch_assoc()) {
            $provisionamentos[] = $linha;
        }
    }
} catch (Throwable $erro) {
    $erroBanco = 'Nao foi possivel carregar o ambiente. Importe database/update_codex_3.sql se ainda nao fez isso.';
}

renderAdminTopo('Ambiente da Empresa');
?>
    <main class="admin-main">
        <div class="container">
            <div class="admin-heading">
                <div>
                    <span class="section-kicker">Ambiente</span>
                    <h1>Ambiente da empresa</h1>
                </div>
                <a class="btn btn-outline-primary" href="empresas.php"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
            </div>

            <?php if (!empty($_GET['sucesso'])): ?>
                <div class="alert alert-success" role="alert">Ambiente criado com sucesso.</div>
            <?php endif; ?>

            <?php if ($erroBanco): ?>
                <div class="alert alert-warning" role="alert"><?= e($erroBanco); ?></div>
            <?php endif; ?>

            <?php if (!$empresa): ?>
                <div class="alert alert-warning" role="alert">Empresa nao encontrada.</div>
            <?php else: ?>
                <div class="row g-4">
                    <div class="col-xl-5">
                        <section class="admin-panel h-100">
                            <div class="panel-header">
                                <div>
                                    <h2><?= e($empresa['nome_empresa']); ?></h2>
                                    <p><?= e($empresa['slug']); ?></p>
                                </div>
                                <?= badgeStatus((string) $empresa['status']); ?>
                            </div>
                            <div class="summary-list">
                                <div><span>Subdominio</span><strong><?= e($empresa['subdominio'] ?: 'Nao definido'); ?></strong></div>
                                <div><span>Nome do banco</span><strong><?= e($empresa['nome_banco'] ?: 'Nao informado'); ?></strong></div>
                                <div><span>Ambiente criado</span><strong><?= (int) ($empresa['ambiente_criado'] ?? 0) === 1 ? 'Sim' : 'Nao'; ?></strong></div>
                                <div><span>Data de criacao</span><strong><?= !empty($empresa['data_criacao_ambiente']) ? e(date('d/m/Y H:i', strtotime($empresa['data_criacao_ambiente']))) : 'Nao criado'; ?></strong></div>
                                <div><span>Admin inicial</span><strong><?= e($empresa['admin_cliente_nome'] ?: 'Nao definido'); ?></strong></div>
                                <div><span>E-mail do admin</span><strong><?= e($empresa['admin_cliente_email'] ?: 'Nao definido'); ?></strong></div>
                                <div><span>Plano</span><strong><?= e($empresa['nome_plano'] ?: 'Sem plano'); ?></strong></div>
                            </div>

                            <?php if (!empty($empresa['erro_provisionamento'])): ?>
                                <div class="alert alert-danger mt-3 mb-0" role="alert"><?= e($empresa['erro_provisionamento']); ?></div>
                            <?php endif; ?>
                        </section>
                    </div>

                    <div class="col-xl-7">
                        <section class="admin-panel h-100">
                            <div class="panel-header">
                                <div>
                                    <h2>Provisionamentos recentes</h2>
                                    <p>Historico das ultimas etapas executadas.</p>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Data/hora</th>
                                            <th>Status</th>
                                            <th>Etapa</th>
                                            <th>Mensagem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!$provisionamentos): ?>
                                            <tr><td colspan="4" class="text-muted">Nenhum provisionamento registrado.</td></tr>
                                        <?php endif; ?>
                                        <?php foreach ($provisionamentos as $item): ?>
                                            <tr>
                                                <td><?= e(date('d/m/Y H:i', strtotime($item['criado_em']))); ?></td>
                                                <td><?= badgeStatus((string) $item['status']); ?></td>
                                                <td><?= e($item['etapa']); ?></td>
                                                <td class="lead-message"><?= nl2br(e($item['mensagem'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
<?php renderAdminRodape(); ?>
