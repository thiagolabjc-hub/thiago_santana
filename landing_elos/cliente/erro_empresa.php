<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/conexao.php';

$codigo = trim((string) ($_GET['codigo'] ?? 'erro'));
$mensagem = trim((string) ($_GET['mensagem'] ?? 'Nao foi possivel acessar o ambiente da empresa.'));
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ambiente indisponivel | ELOS</title>
    <link rel="icon" type="image/png" href="../assets/img/elos-favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="tenant-page">
    <main class="auth-shell">
        <section class="auth-card">
            <div class="auth-brand">
                <span class="brand-mark"><img src="../assets/img/elos-favicon.png" alt=""></span>
                <div>
                    <strong>ELOS</strong>
                    <small>Area do cliente</small>
                </div>
            </div>
            <h1>Ambiente indisponivel</h1>
            <p><?= e($mensagem); ?></p>
            <div class="alert alert-light border" role="alert">
                Codigo: <?= e($codigo); ?>
            </div>
            <a class="btn btn-primary w-100" href="../index.php">Voltar para a landing</a>
        </section>
    </main>
</body>
</html>
