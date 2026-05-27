<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/tenant_conexao.php';

$bancoAtual = '';

try {
    $resultadoBanco = $connCliente->query('SELECT DATABASE() AS banco_atual');
    $linhaBanco = $resultadoBanco->fetch_assoc();
    $bancoAtual = (string) ($linhaBanco['banco_atual'] ?? '');
} catch (Throwable $erro) {
    $bancoAtual = 'Nao foi possivel consultar o banco atual.';
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($empresaAtual['nome_empresa']); ?> | Area do Cliente ELOS</title>
    <link rel="icon" type="image/png" href="../assets/img/elos-favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="tenant-page">
    <main class="tenant-shell">
        <section class="tenant-panel">
            <div class="tenant-brand">
                <span class="brand-mark"><img src="../assets/img/elos-favicon.png" alt=""></span>
                <div>
                    <strong>ELOS Cliente</strong>
                    <small>Conexao dinamica por empresa</small>
                </div>
            </div>

            <div class="tenant-heading">
                <span class="section-kicker">Ambiente conectado</span>
                <h1><?= e($empresaAtual['nome_empresa']); ?></h1>
                <p>Esta tela valida a identificacao da empresa e a conexao ao banco individual.</p>
            </div>

            <div class="summary-list">
                <div><span>Empresa</span><strong><?= e($empresaAtual['nome_empresa']); ?></strong></div>
                <div><span>Slug</span><strong><?= e($empresaAtual['slug']); ?></strong></div>
                <div><span>Banco conectado</span><strong><?= e($empresaAtual['nome_banco']); ?></strong></div>
                <div><span>Status</span><strong><?= e($empresaAtual['status']); ?></strong></div>
                <div><span>Plano</span><strong><?= e($empresaAtual['nome_plano'] ?: 'Sem plano'); ?></strong></div>
                <div><span>SELECT DATABASE()</span><strong><?= e($bancoAtual); ?></strong></div>
            </div>

            <div class="tenant-actions">
                <a class="btn btn-primary" href="login.php?empresa=<?= e($empresaAtual['slug']); ?>"><i class="fa-solid fa-right-to-bracket me-2"></i>Ir para login</a>
            </div>
        </section>
    </main>
</body>
</html>
