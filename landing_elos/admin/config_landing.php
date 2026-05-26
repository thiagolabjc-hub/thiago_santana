<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/conexao.php';

iniciarSessaoAdminElos();

if (empty($_SESSION['landing_elos_admin_id'])) {
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['landing_elos_csrf'])) {
    $_SESSION['landing_elos_csrf'] = bin2hex(random_bytes(32));
}

$config = landingConfigPadrao();
$erroBanco = '';

try {
    $conexao = obterConexao();
    $resultado = $conexao->query('SELECT * FROM landing_config ORDER BY id DESC LIMIT 1');
    $linha = $resultado->fetch_assoc();

    if ($linha) {
        $config = array_merge($config, $linha);
    }
} catch (Throwable $erro) {
    $erroBanco = 'Nao foi possivel carregar a configuracao salva.';
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configurar landing | ELOS</title>
    <link rel="icon" type="image/png" href="../assets/img/elos-favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="admin-page">
    <nav class="admin-topbar">
        <div class="container">
            <div class="admin-topbar-row">
                <a class="admin-logo" href="dashboard.php">
                    <span class="brand-mark"><img src="../assets/img/elos-favicon.png" alt=""></span>
                    <span>ELOS Master</span>
                </a>
                <div class="admin-user">
                    <a class="btn btn-outline-primary btn-sm" href="../index.php" target="_blank" rel="noopener">Ver landing</a>
                    <a class="btn btn-primary btn-sm" href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket me-1"></i>Sair</a>
                </div>
            </div>
            <div class="admin-menu">
                <a class="admin-menu-link" href="dashboard.php"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
                <a class="admin-menu-link active" href="config_landing.php"><i class="fa-solid fa-pen-to-square"></i><span>Landing</span></a>
                <a class="admin-menu-link" href="leads.php"><i class="fa-solid fa-users"></i><span>Leads</span></a>
                <a class="admin-menu-link" href="empresas.php"><i class="fa-solid fa-building"></i><span>Empresas</span></a>
                <a class="admin-menu-link" href="planos.php"><i class="fa-solid fa-layer-group"></i><span>Planos</span></a>
                <a class="admin-menu-link" href="contratos.php"><i class="fa-solid fa-file-contract"></i><span>Contratos</span></a>
                <a class="admin-menu-link" href="logs_master.php"><i class="fa-solid fa-clock-rotate-left"></i><span>Logs Master</span></a>
            </div>
        </div>
    </nav>

    <main class="admin-main">
        <div class="container">
            <div class="admin-heading">
                <div>
                    <span class="section-kicker">Configuracao</span>
                    <h1>Editar landing page</h1>
                </div>
                <a class="btn btn-outline-primary" href="../index.php" target="_blank" rel="noopener"><i class="fa-solid fa-up-right-from-square me-2"></i>Visualizar</a>
            </div>

            <?php if (!empty($_GET['sucesso'])): ?>
                <div class="alert alert-success" role="alert">Configuracao salva com sucesso.</div>
            <?php endif; ?>

            <?php if (!empty($_GET['erro'])): ?>
                <div class="alert alert-danger" role="alert"><?= e($_GET['erro']); ?></div>
            <?php endif; ?>

            <?php if ($erroBanco): ?>
                <div class="alert alert-warning" role="alert"><?= e($erroBanco); ?></div>
            <?php endif; ?>

            <form class="admin-panel config-form" method="post" action="salvar_config_landing.php" enctype="multipart/form-data">
                <input type="hidden" name="csrf" value="<?= e($_SESSION['landing_elos_csrf']); ?>">
                <input type="hidden" name="id" value="<?= e($config['id']); ?>">
                <input type="hidden" name="imagem_atual" value="<?= e($config['imagem_banner']); ?>">

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="form-section">
                            <h2>Conteudo principal</h2>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="nome_sistema">Nome do sistema</label>
                                    <input class="form-control" id="nome_sistema" name="nome_sistema" value="<?= e($config['nome_sistema']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="slogan">Slogan</label>
                                    <input class="form-control" id="slogan" name="slogan" value="<?= e($config['slogan']); ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="titulo_principal">Titulo principal</label>
                                    <input class="form-control" id="titulo_principal" name="titulo_principal" value="<?= e($config['titulo_principal']); ?>" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="subtitulo">Subtitulo</label>
                                    <textarea class="form-control" id="subtitulo" name="subtitulo" rows="3"><?= e($config['subtitulo']); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h2>Botoes</h2>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="texto_botao_principal">Texto do botao principal</label>
                                    <input class="form-control" id="texto_botao_principal" name="texto_botao_principal" value="<?= e($config['texto_botao_principal']); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="link_botao_principal">Link do botao principal</label>
                                    <input class="form-control" id="link_botao_principal" name="link_botao_principal" value="<?= e($config['link_botao_principal']); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="texto_botao_secundario">Texto do botao secundario</label>
                                    <input class="form-control" id="texto_botao_secundario" name="texto_botao_secundario" value="<?= e($config['texto_botao_secundario']); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="link_botao_secundario">Link do botao secundario</label>
                                    <input class="form-control" id="link_botao_secundario" name="link_botao_secundario" value="<?= e($config['link_botao_secundario']); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h2>Secoes da pagina</h2>
                            <div class="mb-3">
                                <label class="form-label" for="descricao_sobre">Descricao sobre o sistema</label>
                                <textarea class="form-control" id="descricao_sobre" name="descricao_sobre" rows="4"><?= e($config['descricao_sobre']); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="problemas_resolvidos">Problemas resolvidos</label>
                                <textarea class="form-control" id="problemas_resolvidos" name="problemas_resolvidos" rows="5"><?= e($config['problemas_resolvidos']); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="funcionalidades">Funcionalidades</label>
                                <textarea class="form-control" id="funcionalidades" name="funcionalidades" rows="5"><?= e($config['funcionalidades']); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="diferenciais">Diferenciais</label>
                                <textarea class="form-control" id="diferenciais" name="diferenciais" rows="5"><?= e($config['diferenciais']); ?></textarea>
                            </div>
                            <div>
                                <label class="form-label" for="texto_chamada_final">Chamada final</label>
                                <textarea class="form-control" id="texto_chamada_final" name="texto_chamada_final" rows="3"><?= e($config['texto_chamada_final']); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-section sticky-side">
                            <h2>Aparencia e contato</h2>
                            <div class="mb-3">
                                <label class="form-label" for="cor_principal">Cor principal</label>
                                <input class="form-control form-control-color" id="cor_principal" name="cor_principal" type="color" value="<?= e(corCssSegura((string) $config['cor_principal'], '#2563eb')); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="cor_secundaria">Cor secundaria</label>
                                <input class="form-control form-control-color" id="cor_secundaria" name="cor_secundaria" type="color" value="<?= e(corCssSegura((string) $config['cor_secundaria'], '#14b8a6')); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="imagem_banner">Imagem/banner</label>
                                <input class="form-control" id="imagem_banner" name="imagem_banner" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                                <?php if (!empty($config['imagem_banner'])): ?>
                                    <img class="banner-preview" src="../<?= e($config['imagem_banner']); ?>" alt="Banner atual">
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="whatsapp">WhatsApp</label>
                                <input class="form-control" id="whatsapp" name="whatsapp" value="<?= e($config['whatsapp']); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="email_contato">E-mail de contato</label>
                                <input class="form-control" id="email_contato" name="email_contato" type="email" value="<?= e($config['email_contato']); ?>">
                            </div>
                            <div class="mb-4">
                                <label class="form-label" for="status">Status da landing</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="ativo" <?= $config['status'] === 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                                    <option value="inativo" <?= $config['status'] === 'inativo' ? 'selected' : ''; ?>>Inativo</option>
                                </select>
                            </div>
                            <button class="btn btn-primary w-100" type="submit">
                                <i class="fa-solid fa-floppy-disk me-2"></i>Salvar configuracao
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
