<?php
declare(strict_types=1);

require_once __DIR__ . '/config/conexao.php';

$config = carregarConfiguracaoPublica();
$corPrincipal = corCssSegura((string) $config['cor_principal'], '#2563eb');
$corSecundaria = corCssSegura((string) $config['cor_secundaria'], '#14b8a6');
$mensagemSucesso = '';
$mensagemErro = '';
$valoresFormulario = [
    'nome' => '',
    'empresa' => '',
    'email' => '',
    'telefone' => '',
    'mensagem' => '',
];

function iconeFuncionalidade(string $texto): string
{
    $textoNormalizado = function_exists('iconv') ? iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto) : $texto;
    $textoNormalizado = strtolower($textoNormalizado ?: $texto);

    if (strpos($textoNormalizado, 'pend') !== false) {
        return 'fa-list-check';
    }

    if (strpos($textoNormalizado, 'autoriz') !== false) {
        return 'fa-file-signature';
    }

    if (strpos($textoNormalizado, 'chamado') !== false) {
        return 'fa-headset';
    }

    if (strpos($textoNormalizado, 'clinica') !== false) {
        return 'fa-hospital-user';
    }

    if (strpos($textoNormalizado, 'motoboy') !== false) {
        return 'fa-motorcycle';
    }

    if (strpos($textoNormalizado, 'glosa') !== false) {
        return 'fa-file-invoice-dollar';
    }

    if (strpos($textoNormalizado, 'relatorio') !== false) {
        return 'fa-chart-line';
    }

    if (strpos($textoNormalizado, 'usuario') !== false) {
        return 'fa-user-shield';
    }

    if (strpos($textoNormalizado, 'indicador') !== false) {
        return 'fa-gauge-high';
    }

    return 'fa-circle-nodes';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'lead') {
    foreach ($valoresFormulario as $campo => $valor) {
        $valoresFormulario[$campo] = trim((string) ($_POST[$campo] ?? ''));
    }

    if (
        $valoresFormulario['nome'] === '' ||
        $valoresFormulario['empresa'] === '' ||
        $valoresFormulario['email'] === '' ||
        $valoresFormulario['telefone'] === '' ||
        $valoresFormulario['mensagem'] === ''
    ) {
        $mensagemErro = 'Preencha todos os campos para solicitar a demonstracao.';
    } elseif (!filter_var($valoresFormulario['email'], FILTER_VALIDATE_EMAIL)) {
        $mensagemErro = 'Informe um e-mail valido para contato.';
    } else {
        try {
            $conexao = obterConexao();
            $stmt = $conexao->prepare(
                "INSERT INTO leads (nome, empresa, email, telefone, mensagem, origem, status, criado_em)
                 VALUES (?, ?, ?, ?, ?, 'landing_page', 'novo', NOW())"
            );
            $stmt->bind_param(
                'sssss',
                $valoresFormulario['nome'],
                $valoresFormulario['empresa'],
                $valoresFormulario['email'],
                $valoresFormulario['telefone'],
                $valoresFormulario['mensagem']
            );
            $stmt->execute();

            $mensagemSucesso = 'Solicitacao enviada com sucesso. Nossa equipe entrara em contato em breve.';

            foreach ($valoresFormulario as $campo => $valor) {
                $valoresFormulario[$campo] = '';
            }
        } catch (Throwable $erro) {
            $mensagemErro = 'Nao foi possivel enviar sua solicitacao agora. Tente novamente em instantes.';
        }
    }
}

$problemas = textoParaItens((string) $config['problemas_resolvidos']);
$funcionalidades = textoParaItens((string) $config['funcionalidades']);
$diferenciais = textoParaItens((string) $config['diferenciais']);
$imagemBanner = trim((string) $config['imagem_banner']);
$imagemBannerValida = $imagemBanner !== '' && file_exists(__DIR__ . '/' . $imagemBanner);
$digitosWhatsApp = preg_replace('/\D+/', '', (string) $config['whatsapp']);
$linkWhatsApp = $digitosWhatsApp ? 'https://wa.me/' . $digitosWhatsApp : '';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($config['subtitulo']); ?>">
    <title><?= e($config['nome_sistema']); ?> | <?= e($config['slogan']); ?></title>
    <link rel="icon" type="image/png" href="assets/img/elos-favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        :root {
            --cor-principal: <?= e($corPrincipal); ?>;
            --cor-secundaria: <?= e($corSecundaria); ?>;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg landing-nav fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="#inicio" aria-label="<?= e($config['nome_sistema']); ?>">
                <span class="brand-mark"><img src="assets/img/elos-favicon.png" alt=""></span>
                <span><?= e($config['nome_sistema']); ?></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal" aria-controls="menuPrincipal" aria-expanded="false" aria-label="Abrir menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menuPrincipal">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item"><a class="nav-link" href="#sobre">Sobre</a></li>
                    <li class="nav-item"><a class="nav-link" href="#funcionalidades">Funcionalidades</a></li>
                    <li class="nav-item"><a class="nav-link" href="#diferenciais">Diferenciais</a></li>
                    <li class="nav-item"><a class="btn btn-primary btn-sm nav-cta" href="#demonstracao"><?= e($config['texto_botao_principal']); ?></a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main id="inicio">
        <section class="hero-section">
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6">
                        <span class="eyebrow"><?= e($config['slogan']); ?></span>
                        <h1><?= e($config['titulo_principal']); ?></h1>
                        <p class="hero-subtitle"><?= e($config['subtitulo']); ?></p>
                        <div class="hero-actions">
                            <a class="btn btn-primary btn-lg" href="<?= e($config['link_botao_principal']); ?>">
                                <i class="fa-solid fa-calendar-check me-2"></i><?= e($config['texto_botao_principal']); ?>
                            </a>
                            <a class="btn btn-outline-primary btn-lg" href="<?= e($config['link_botao_secundario']); ?>">
                                <?= e($config['texto_botao_secundario']); ?>
                            </a>
                        </div>
                        <div class="hero-proof">
                            <span><i class="fa-solid fa-shield-halved"></i> Operacao rastreavel</span>
                            <span><i class="fa-solid fa-chart-simple"></i> Indicadores em tempo real</span>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <?php if ($imagemBannerValida): ?>
                            <img class="hero-image" src="<?= e($imagemBanner); ?>" alt="Banner do sistema <?= e($config['nome_sistema']); ?>">
                        <?php else: ?>
                            <div class="platform-preview" aria-label="Previa visual do painel ELOS">
                                <div class="preview-toolbar">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                                <div class="preview-grid">
                                    <div class="preview-kpi">
                                        <small>Pendencias</small>
                                        <strong>128</strong>
                                        <span class="text-success">-18% no ciclo</span>
                                    </div>
                                    <div class="preview-kpi">
                                        <small>Autorizacoes</small>
                                        <strong>94%</strong>
                                        <span class="text-primary">resolvidas</span>
                                    </div>
                                    <div class="preview-panel wide">
                                        <div class="preview-line w-75"></div>
                                        <div class="preview-line w-50"></div>
                                        <div class="preview-progress"><span style="width: 72%"></span></div>
                                    </div>
                                    <div class="preview-panel">
                                        <i class="fa-solid fa-headset"></i>
                                        <span>Chamados</span>
                                    </div>
                                    <div class="preview-panel">
                                        <i class="fa-solid fa-route"></i>
                                        <span>Motoboys</span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-block" id="sobre">
            <div class="container">
                <div class="row align-items-center g-4">
                    <div class="col-lg-5">
                        <span class="section-kicker">Sobre o sistema</span>
                        <h2>Uma camada digital para conectar toda a operacao.</h2>
                    </div>
                    <div class="col-lg-7">
                        <p class="section-text mb-0"><?= nl2br(e($config['descricao_sobre'])); ?></p>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-block muted-section">
            <div class="container">
                <div class="section-heading">
                    <span class="section-kicker">Problemas resolvidos</span>
                    <h2>Menos retrabalho, mais controle operacional.</h2>
                </div>
                <div class="row g-3">
                    <?php foreach ($problemas as $problema): ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="info-card h-100">
                                <i class="fa-solid fa-circle-check"></i>
                                <p><?= e($problema); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="section-block" id="funcionalidades">
            <div class="container">
                <div class="section-heading">
                    <span class="section-kicker">Funcionalidades</span>
                    <h2>Recursos centrais para uma operacao B2B de saude.</h2>
                </div>
                <div class="row g-3">
                    <?php foreach ($funcionalidades as $funcionalidade): ?>
                        <div class="col-sm-6 col-lg-4">
                            <div class="feature-card h-100">
                                <span class="feature-icon"><i class="fa-solid <?= e(iconeFuncionalidade($funcionalidade)); ?>"></i></span>
                                <h3><?= e($funcionalidade); ?></h3>
                                <p>Organize o fluxo, acompanhe prazos e mantenha as equipes alinhadas com visibilidade operacional.</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="section-block muted-section" id="diferenciais">
            <div class="container">
                <div class="row g-4 align-items-start">
                    <div class="col-lg-5">
                        <span class="section-kicker">Diferenciais</span>
                        <h2>Desenhado para crescer junto com a sua operacao.</h2>
                    </div>
                    <div class="col-lg-7">
                        <div class="differential-list">
                            <?php foreach ($diferenciais as $diferencial): ?>
                                <div class="differential-item">
                                    <i class="fa-solid fa-arrow-right"></i>
                                    <span><?= e($diferencial); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-block cta-section" id="demonstracao">
            <div class="container">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-5">
                        <span class="section-kicker">Solicitar demonstracao</span>
                        <h2><?= e($config['texto_chamada_final']); ?></h2>
                        <p class="section-text">Envie seus dados e fale com nossa equipe para entender como o ELOS pode se adaptar ao seu fluxo.</p>
                        <div class="contact-links">
                            <?php if ($linkWhatsApp): ?>
                                <a href="<?= e($linkWhatsApp); ?>" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
                            <?php endif; ?>
                            <?php if (!empty($config['email_contato'])): ?>
                                <a href="mailto:<?= e($config['email_contato']); ?>"><i class="fa-regular fa-envelope"></i> <?= e($config['email_contato']); ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <form class="lead-form" method="post" action="#demonstracao" novalidate>
                            <input type="hidden" name="acao" value="lead">

                            <div class="form-heading">
                                <h3>Formulario de contato</h3>
                                <p>Preencha os dados abaixo para solicitar uma demonstracao.</p>
                            </div>

                            <?php if ($mensagemSucesso): ?>
                                <div class="alert alert-success" role="alert"><?= e($mensagemSucesso); ?></div>
                            <?php endif; ?>

                            <?php if ($mensagemErro): ?>
                                <div class="alert alert-danger" role="alert"><?= e($mensagemErro); ?></div>
                            <?php endif; ?>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="nome">Nome</label>
                                    <input class="form-control" id="nome" name="nome" type="text" value="<?= e($valoresFormulario['nome']); ?>" placeholder="Seu nome completo" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="empresa">Empresa</label>
                                    <input class="form-control" id="empresa" name="empresa" type="text" value="<?= e($valoresFormulario['empresa']); ?>" placeholder="Nome da empresa ou laboratorio" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="email">E-mail</label>
                                    <input class="form-control" id="email" name="email" type="email" value="<?= e($valoresFormulario['email']); ?>" placeholder="seuemail@empresa.com.br" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="telefone">Telefone</label>
                                    <input class="form-control" id="telefone" name="telefone" type="text" value="<?= e($valoresFormulario['telefone']); ?>" placeholder="(00) 00000-0000" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="mensagem">Mensagem</label>
                                    <textarea class="form-control" id="mensagem" name="mensagem" rows="4" placeholder="Conte um pouco sobre sua operacao e o que deseja melhorar" required><?= e($valoresFormulario['mensagem']); ?></textarea>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-primary btn-lg w-100" type="submit">
                                        <i class="fa-solid fa-paper-plane me-2"></i>Enviar solicitacao
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <strong><?= e($config['nome_sistema']); ?></strong>
                    <span><?= e($config['slogan']); ?></span>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="admin/login.php">Acesso administrativo</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
