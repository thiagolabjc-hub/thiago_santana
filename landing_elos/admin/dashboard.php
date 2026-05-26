<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/master.php';

iniciarAdmin();

$metricas = [
    'total_leads' => 0,
    'total_empresas' => 0,
    'empresas_ativas' => 0,
    'empresas_implantacao' => 0,
    'empresas_suspensas' => 0,
    'ambientes_criados' => 0,
    'ambientes_pendentes' => 0,
    'planos_ativos' => 0,
    'contratos_ativos' => 0,
];
$ultimosLeads = [];
$empresasRecentes = [];
$empresasProvisionadas = [];
$erroBanco = '';

function contarDashboard(mysqli $conexao, string $sql): int
{
    try {
        $resultado = $conexao->query($sql);
        $linha = $resultado->fetch_assoc();

        return (int) ($linha['total'] ?? 0);
    } catch (Throwable $erro) {
        return 0;
    }
}

try {
    $conexao = obterConexao();

    $metricas['total_leads'] = contarDashboard($conexao, 'SELECT COUNT(*) AS total FROM leads');
    $metricas['total_empresas'] = contarDashboard($conexao, 'SELECT COUNT(*) AS total FROM empresas');
    $metricas['empresas_ativas'] = contarDashboard($conexao, "SELECT COUNT(*) AS total FROM empresas WHERE status = 'ATIVA'");
    $metricas['empresas_implantacao'] = contarDashboard($conexao, "SELECT COUNT(*) AS total FROM empresas WHERE status = 'EM_IMPLANTACAO'");
    $metricas['empresas_suspensas'] = contarDashboard($conexao, "SELECT COUNT(*) AS total FROM empresas WHERE status = 'SUSPENSA'");
    $metricas['ambientes_criados'] = contarDashboard($conexao, 'SELECT COUNT(*) AS total FROM empresas WHERE ambiente_criado = 1');
    $metricas['ambientes_pendentes'] = contarDashboard($conexao, 'SELECT COUNT(*) AS total FROM empresas WHERE COALESCE(ambiente_criado, 0) = 0');
    $metricas['planos_ativos'] = contarDashboard($conexao, 'SELECT COUNT(*) AS total FROM planos WHERE status = 1');
    $metricas['contratos_ativos'] = contarDashboard($conexao, "SELECT COUNT(*) AS total FROM contratos WHERE status = 'ATIVO'");

    $resultadoLeads = $conexao->query(
        'SELECT nome, empresa, email, status, criado_em
         FROM leads
         ORDER BY criado_em DESC
         LIMIT 5'
    );

    while ($lead = $resultadoLeads->fetch_assoc()) {
        $ultimosLeads[] = $lead;
    }

    try {
        $resultadoEmpresas = $conexao->query(
            'SELECT e.id, e.nome_empresa, e.slug, e.status, e.criado_em, p.nome_plano
             FROM empresas e
             LEFT JOIN planos p ON p.id = e.plano_id
             ORDER BY e.criado_em DESC
             LIMIT 5'
        );

        while ($empresa = $resultadoEmpresas->fetch_assoc()) {
            $empresasRecentes[] = $empresa;
        }
    } catch (Throwable $erroEmpresas) {
        $empresasRecentes = [];
    }

    try {
        $resultadoProvisionadas = $conexao->query(
            'SELECT e.id, e.nome_empresa, e.slug, e.nome_banco, e.data_criacao_ambiente, p.nome_plano
             FROM empresas e
             LEFT JOIN planos p ON p.id = e.plano_id
             WHERE e.ambiente_criado = 1
             ORDER BY e.data_criacao_ambiente DESC
             LIMIT 5'
        );

        while ($empresaProvisionada = $resultadoProvisionadas->fetch_assoc()) {
            $empresasProvisionadas[] = $empresaProvisionada;
        }
    } catch (Throwable $erroProvisionadas) {
        $empresasProvisionadas = [];
    }
} catch (Throwable $erro) {
    $erroBanco = 'Nao foi possivel carregar os dados do painel.';
}

renderAdminTopo('Dashboard');
?>
    <main class="admin-main">
        <div class="container">
            <div class="admin-heading">
                <div>
                    <span class="section-kicker">Painel Master</span>
                    <h1>Base comercial ELOS</h1>
                </div>
                <div class="admin-actions">
                    <a class="btn btn-outline-primary" href="nova_empresa.php"><i class="fa-solid fa-building-circle-plus me-2"></i>Nova Empresa</a>
                    <a class="btn btn-primary" href="novo_contrato.php"><i class="fa-solid fa-file-circle-plus me-2"></i>Novo Contrato</a>
                </div>
            </div>

            <?php if ($erroBanco): ?>
                <div class="alert alert-warning" role="alert"><?= e($erroBanco); ?></div>
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-sm-6 col-xl-3">
                    <div class="metric-card">
                        <span>Total de leads</span>
                        <strong><?= e($metricas['total_leads']); ?></strong>
                        <small>Solicitacoes comerciais</small>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="metric-card">
                        <span>Total de empresas</span>
                        <strong><?= e($metricas['total_empresas']); ?></strong>
                        <small>Clientes cadastrados</small>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="metric-card">
                        <span>Empresas ativas</span>
                        <strong><?= e($metricas['empresas_ativas']); ?></strong>
                        <small>Operando comercialmente</small>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="metric-card">
                        <span>Em implantacao</span>
                        <strong><?= e($metricas['empresas_implantacao']); ?></strong>
                        <small>Novas contas em setup</small>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="metric-card">
                        <span>Suspensas</span>
                        <strong><?= e($metricas['empresas_suspensas']); ?></strong>
                        <small>Contas com restricao</small>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="metric-card">
                        <span>Planos ativos</span>
                        <strong><?= e($metricas['planos_ativos']); ?></strong>
                        <small>Ofertas comercializaveis</small>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="metric-card">
                        <span>Ambientes criados</span>
                        <strong><?= e($metricas['ambientes_criados']); ?></strong>
                        <small>Bancos de cliente provisionados</small>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="metric-card">
                        <span>Ambientes pendentes</span>
                        <strong><?= e($metricas['ambientes_pendentes']); ?></strong>
                        <small>Empresas aguardando base propria</small>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="metric-card">
                        <span>Contratos ativos</span>
                        <strong><?= e($metricas['contratos_ativos']); ?></strong>
                        <small>Receita contratada</small>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="metric-card metric-card-action">
                        <span>Atalhos</span>
                        <a href="empresas.php">Empresas</a>
                        <a href="planos.php">Planos</a>
                        <a href="logs_master.php">Logs Master</a>
                    </div>
                </div>
            </div>

            <div class="row g-4 mt-1">
                <div class="col-xl-6">
                    <section class="admin-panel h-100">
                        <div class="panel-header">
                            <div>
                                <h2>Leads recentes</h2>
                                <p>Ultimas solicitacoes recebidas pela landing.</p>
                            </div>
                            <a href="leads.php">Ver todos</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Empresa</th>
                                        <th>Status</th>
                                        <th>Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!$ultimosLeads): ?>
                                        <tr><td colspan="4" class="text-muted">Nenhum lead recebido ainda.</td></tr>
                                    <?php endif; ?>
                                    <?php foreach ($ultimosLeads as $lead): ?>
                                        <tr>
                                            <td>
                                                <strong><?= e($lead['nome']); ?></strong><br>
                                                <small><?= e($lead['email']); ?></small>
                                            </td>
                                            <td><?= e($lead['empresa']); ?></td>
                                            <td><?= badgeStatus((string) $lead['status']); ?></td>
                                            <td><?= e(date('d/m/Y H:i', strtotime($lead['criado_em']))); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
                <div class="col-xl-6">
                    <section class="admin-panel h-100">
                        <div class="panel-header">
                            <div>
                                <h2>Empresas recentes</h2>
                                <p>Novas contas cadastradas no master comercial.</p>
                            </div>
                            <a href="empresas.php">Ver todas</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Empresa</th>
                                        <th>Plano</th>
                                        <th>Status</th>
                                        <th>Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!$empresasRecentes): ?>
                                        <tr><td colspan="4" class="text-muted">Nenhuma empresa cadastrada ainda.</td></tr>
                                    <?php endif; ?>
                                    <?php foreach ($empresasRecentes as $empresa): ?>
                                        <tr>
                                            <td>
                                                <strong><?= e($empresa['nome_empresa']); ?></strong><br>
                                                <small><?= e($empresa['slug']); ?></small>
                                            </td>
                                            <td><?= e($empresa['nome_plano'] ?: 'Sem plano'); ?></td>
                                            <td><?= badgeStatus((string) $empresa['status']); ?></td>
                                            <td><?= e(date('d/m/Y', strtotime($empresa['criado_em']))); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>

            <section class="admin-panel mt-4">
                <div class="panel-header">
                    <div>
                        <h2>Ultimas empresas provisionadas</h2>
                        <p>Ambientes individuais criados pelo painel master.</p>
                    </div>
                    <a href="provisionamentos.php">Ver provisionamentos</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Empresa</th>
                                <th>Plano</th>
                                <th>Banco</th>
                                <th>Data</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$empresasProvisionadas): ?>
                                <tr><td colspan="5" class="text-muted">Nenhuma empresa provisionada ainda.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($empresasProvisionadas as $empresaProvisionada): ?>
                                <tr>
                                    <td>
                                        <strong><?= e($empresaProvisionada['nome_empresa']); ?></strong><br>
                                        <small><?= e($empresaProvisionada['slug']); ?></small>
                                    </td>
                                    <td><?= e($empresaProvisionada['nome_plano'] ?: 'Sem plano'); ?></td>
                                    <td><?= e($empresaProvisionada['nome_banco']); ?></td>
                                    <td><?= e(date('d/m/Y H:i', strtotime($empresaProvisionada['data_criacao_ambiente']))); ?></td>
                                    <td><a class="btn btn-outline-primary btn-sm" href="ambiente_empresa.php?id=<?= e($empresaProvisionada['id']); ?>"><i class="fa-solid fa-server me-1"></i>Ver</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
<?php renderAdminRodape(); ?>
