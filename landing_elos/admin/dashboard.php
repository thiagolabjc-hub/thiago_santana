<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/conexao.php';

session_name('LANDING_ELOS_ADMIN');
session_start();

if (empty($_SESSION['landing_elos_admin_id'])) {
    header('Location: login.php');
    exit;
}

$config = landingConfigPadrao();
$totalLeads = 0;
$ultimosLeads = [];
$erroBanco = '';

try {
    $conexao = obterConexao();
    $resultadoTotal = $conexao->query('SELECT COUNT(*) AS total FROM leads');
    $totalLeads = (int) ($resultadoTotal->fetch_assoc()['total'] ?? 0);

    $resultadoConfig = $conexao->query('SELECT * FROM landing_config ORDER BY id DESC LIMIT 1');
    $linhaConfig = $resultadoConfig->fetch_assoc();
    if ($linhaConfig) {
        $config = array_merge($config, $linhaConfig);
    }

    $resultadoLeads = $conexao->query(
        'SELECT nome, empresa, email, status, criado_em
         FROM leads
         ORDER BY criado_em DESC
         LIMIT 5'
    );

    while ($lead = $resultadoLeads->fetch_assoc()) {
        $ultimosLeads[] = $lead;
    }
} catch (Throwable $erro) {
    $erroBanco = 'Nao foi possivel carregar os dados do painel.';
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | ELOS</title>
    <link rel="icon" type="image/png" href="../assets/img/elos-favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="admin-page">
    <nav class="admin-topbar">
        <div class="container d-flex align-items-center justify-content-between gap-3">
            <a class="admin-logo" href="dashboard.php">
                <span class="brand-mark"><img src="../assets/img/elos-favicon.png" alt=""></span>
                <span>ELOS Admin</span>
            </a>
            <div class="admin-user">
                <span><?= e($_SESSION['landing_elos_admin_nome'] ?? 'Administrador'); ?></span>
                <a class="btn btn-outline-primary btn-sm" href="../index.php" target="_blank" rel="noopener">Ver landing</a>
                <a class="btn btn-primary btn-sm" href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket me-1"></i>Sair</a>
            </div>
        </div>
    </nav>

    <main class="admin-main">
        <div class="container">
            <div class="admin-heading">
                <div>
                    <span class="section-kicker">Dashboard</span>
                    <h1>Painel da landing page</h1>
                </div>
                <div class="admin-actions">
                    <a class="btn btn-outline-primary" href="config_landing.php"><i class="fa-solid fa-pen-to-square me-2"></i>Editar landing</a>
                    <a class="btn btn-primary" href="leads.php"><i class="fa-solid fa-users me-2"></i>Visualizar leads</a>
                </div>
            </div>

            <?php if ($erroBanco): ?>
                <div class="alert alert-warning" role="alert"><?= e($erroBanco); ?></div>
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="metric-card">
                        <span>Total de leads</span>
                        <strong><?= e($totalLeads); ?></strong>
                        <small>Solicitacoes recebidas pela landing</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="metric-card">
                        <span>Status da landing</span>
                        <strong><?= e(ucfirst((string) $config['status'])); ?></strong>
                        <small>Configuracao publica atual</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="metric-card">
                        <span>Sistema</span>
                        <strong><?= e($config['nome_sistema']); ?></strong>
                        <small><?= e($config['slogan']); ?></small>
                    </div>
                </div>
            </div>

            <section class="admin-panel mt-4">
                <div class="panel-header">
                    <div>
                        <h2>Resumo da landing</h2>
                        <p><?= e($config['titulo_principal']); ?></p>
                    </div>
                    <span class="status-pill status-<?= e((string) $config['status']); ?>"><?= e((string) $config['status']); ?></span>
                </div>
                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="summary-box">
                            <span>Chamada principal</span>
                            <p><?= e($config['subtitulo']); ?></p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="summary-box">
                            <span>Chamada final</span>
                            <p><?= e($config['texto_chamada_final']); ?></p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="admin-panel mt-4">
                <div class="panel-header">
                    <div>
                        <h2>Leads recentes</h2>
                        <p>Ultimas solicitacoes recebidas.</p>
                    </div>
                    <a href="leads.php">Ver todos</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Empresa</th>
                                <th>E-mail</th>
                                <th>Status</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$ultimosLeads): ?>
                                <tr><td colspan="5" class="text-muted">Nenhum lead recebido ainda.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($ultimosLeads as $lead): ?>
                                <tr>
                                    <td><?= e($lead['nome']); ?></td>
                                    <td><?= e($lead['empresa']); ?></td>
                                    <td><?= e($lead['email']); ?></td>
                                    <td><span class="status-pill status-<?= e($lead['status']); ?>"><?= e($lead['status']); ?></span></td>
                                    <td><?= e(date('d/m/Y H:i', strtotime($lead['criado_em']))); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
