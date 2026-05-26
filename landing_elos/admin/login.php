<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/conexao.php';

session_name('LANDING_ELOS_ADMIN');
session_start();

if (!empty($_SESSION['landing_elos_admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$erro = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $senha = (string) ($_POST['senha'] ?? '');

    if ($email === '' || $senha === '') {
        $erro = 'Informe e-mail e senha.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Informe um e-mail valido.';
    } else {
        try {
            $conexao = obterConexao();
            $stmt = $conexao->prepare(
                'SELECT id, nome, email, senha, nivel_acesso, status
                 FROM usuarios_admin
                 WHERE email = ?
                 LIMIT 1'
            );
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($idUsuario, $nomeUsuario, $emailUsuario, $senhaHash, $nivelAcesso, $statusUsuario);

            if ($stmt->fetch() && $statusUsuario === 'ativo' && password_verify($senha, $senhaHash)) {
                session_regenerate_id(true);

                $_SESSION['landing_elos_admin_id'] = (int) $idUsuario;
                $_SESSION['landing_elos_admin_nome'] = $nomeUsuario;
                $_SESSION['landing_elos_admin_email'] = $emailUsuario;
                $_SESSION['landing_elos_admin_nivel'] = $nivelAcesso;

                $stmtUpdate = $conexao->prepare('UPDATE usuarios_admin SET ultimo_acesso = NOW() WHERE id = ?');
                $idUsuario = (int) $idUsuario;
                $stmtUpdate->bind_param('i', $idUsuario);
                $stmtUpdate->execute();

                header('Location: dashboard.php');
                exit;
            }

            $erro = 'Credenciais invalidas ou usuario inativo.';
        } catch (Throwable $erroLogin) {
            $erro = 'Nao foi possivel acessar o painel agora.';
        }
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login administrativo | ELOS</title>
    <link rel="icon" type="image/png" href="../assets/img/elos-favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="admin-auth-page">
    <main class="auth-shell">
        <section class="auth-card">
            <div class="auth-brand">
                <span class="brand-mark"><img src="../assets/img/elos-favicon.png" alt=""></span>
                <div>
                    <strong>ELOS</strong>
                    <small>Painel administrativo</small>
                </div>
            </div>
            <h1>Acessar painel</h1>
            <p>Entre com seu usuario administrativo para editar a landing page e acompanhar leads.</p>

            <?php if ($erro): ?>
                <div class="alert alert-danger" role="alert"><?= e($erro); ?></div>
            <?php endif; ?>

            <form method="post" novalidate>
                <div class="mb-3">
                    <label class="form-label" for="email">E-mail</label>
                    <input class="form-control" id="email" name="email" type="email" value="<?= e($email); ?>" required autofocus>
                </div>
                <div class="mb-4">
                    <label class="form-label" for="senha">Senha</label>
                    <input class="form-control" id="senha" name="senha" type="password" required>
                </div>
                <button class="btn btn-primary w-100" type="submit">
                    <i class="fa-solid fa-right-to-bracket me-2"></i>Entrar
                </button>
            </form>
        </section>
    </main>
</body>
</html>
