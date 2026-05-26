<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/conexao.php';

session_name('LANDING_ELOS_ADMIN');
session_start();

if (empty($_SESSION['landing_elos_admin_id'])) {
    header('Location: login.php');
    exit;
}

$leads = [];
$erroBanco = '';

try {
    $conexao = obterConexao();
    $resultado = $conexao->query(
        'SELECT id, nome, empresa, email, telefone, mensagem, origem, status, criado_em
         FROM leads
         ORDER BY criado_em DESC'
    );

    while ($lead = $resultado->fetch_assoc()) {
        $leads[] = $lead;
    }
} catch (Throwable $erro) {
    $erroBanco = 'Nao foi possivel carregar os leads.';
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Leads | ELOS</title>
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
                <a class="btn btn-outline-primary btn-sm" href="dashboard.php"><i class="fa-solid fa-gauge-high me-1"></i>Dashboard</a>
                <a class="btn btn-primary btn-sm" href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket me-1"></i>Sair</a>
            </div>
        </div>
    </nav>

    <main class="admin-main">
        <div class="container">
            <div class="admin-heading">
                <div>
                    <span class="section-kicker">Leads</span>
                    <h1>Solicitacoes de demonstracao</h1>
                </div>
                <a class="btn btn-outline-primary" href="config_landing.php"><i class="fa-solid fa-pen-to-square me-2"></i>Editar landing</a>
            </div>

            <?php if ($erroBanco): ?>
                <div class="alert alert-warning" role="alert"><?= e($erroBanco); ?></div>
            <?php endif; ?>

            <section class="admin-panel">
                <div class="panel-header">
                    <div>
                        <h2>Leads recebidos</h2>
                        <p><?= e(count($leads)); ?> registro(s) encontrado(s).</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Contato</th>
                                <th>Empresa</th>
                                <th>Telefone</th>
                                <th>Mensagem</th>
                                <th>Status</th>
                                <th>Origem</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$leads): ?>
                                <tr><td colspan="8" class="text-muted">Nenhum lead recebido ainda.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($leads as $lead): ?>
                                <tr>
                                    <td><?= e($lead['id']); ?></td>
                                    <td>
                                        <strong><?= e($lead['nome']); ?></strong><br>
                                        <small><?= e($lead['email']); ?></small>
                                    </td>
                                    <td><?= e($lead['empresa']); ?></td>
                                    <td><?= e($lead['telefone']); ?></td>
                                    <td class="lead-message"><?= nl2br(e($lead['mensagem'])); ?></td>
                                    <td><span class="status-pill status-<?= e($lead['status']); ?>"><?= e($lead['status']); ?></span></td>
                                    <td><?= e($lead['origem']); ?></td>
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
