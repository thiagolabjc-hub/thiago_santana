<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/tenant_conexao.php';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | <?= e($empresaAtual['nome_empresa']); ?></title>
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
                    <strong><?= e($empresaAtual['nome_empresa']); ?></strong>
                    <small>Ambiente ELOS</small>
                </div>
            </div>
            <h1>Acessar ambiente</h1>
            <p>A autenticacao real dos usuarios do cliente sera implementada em uma proxima etapa.</p>

            <form novalidate>
                <div class="mb-3">
                    <label class="form-label" for="email">E-mail</label>
                    <input class="form-control" id="email" type="email" placeholder="usuario@empresa.com.br">
                </div>
                <div class="mb-4">
                    <label class="form-label" for="senha">Senha</label>
                    <input class="form-control" id="senha" type="password" placeholder="Senha do usuario">
                </div>
                <button class="btn btn-primary w-100" type="button" disabled>
                    <i class="fa-solid fa-lock me-2"></i>Login em breve
                </button>
            </form>
        </section>
    </main>
</body>
</html>
