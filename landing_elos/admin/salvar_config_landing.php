<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/conexao.php';

session_name('LANDING_ELOS_ADMIN');
session_start();

if (empty($_SESSION['landing_elos_admin_id'])) {
    header('Location: login.php');
    exit;
}

function redirecionarConfigErro(string $mensagem): void
{
    header('Location: config_landing.php?erro=' . urlencode($mensagem));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: config_landing.php');
    exit;
}

$csrfSessao = (string) ($_SESSION['landing_elos_csrf'] ?? '');
$csrfPost = (string) ($_POST['csrf'] ?? '');

if ($csrfSessao === '' || !hash_equals($csrfSessao, $csrfPost)) {
    redirecionarConfigErro('Sessao expirada. Recarregue a pagina e tente novamente.');
}

$id = (int) ($_POST['id'] ?? 0);
$nomeSistema = trim((string) ($_POST['nome_sistema'] ?? ''));
$slogan = trim((string) ($_POST['slogan'] ?? ''));
$tituloPrincipal = trim((string) ($_POST['titulo_principal'] ?? ''));
$subtitulo = trim((string) ($_POST['subtitulo'] ?? ''));
$textoBotaoPrincipal = trim((string) ($_POST['texto_botao_principal'] ?? ''));
$linkBotaoPrincipal = trim((string) ($_POST['link_botao_principal'] ?? ''));
$textoBotaoSecundario = trim((string) ($_POST['texto_botao_secundario'] ?? ''));
$linkBotaoSecundario = trim((string) ($_POST['link_botao_secundario'] ?? ''));
$descricaoSobre = trim((string) ($_POST['descricao_sobre'] ?? ''));
$problemasResolvidos = trim((string) ($_POST['problemas_resolvidos'] ?? ''));
$funcionalidades = trim((string) ($_POST['funcionalidades'] ?? ''));
$diferenciais = trim((string) ($_POST['diferenciais'] ?? ''));
$textoChamadaFinal = trim((string) ($_POST['texto_chamada_final'] ?? ''));
$corPrincipal = trim((string) ($_POST['cor_principal'] ?? '#2563eb'));
$corSecundaria = trim((string) ($_POST['cor_secundaria'] ?? '#14b8a6'));
$whatsapp = trim((string) ($_POST['whatsapp'] ?? ''));
$emailContato = trim((string) ($_POST['email_contato'] ?? ''));
$status = (string) ($_POST['status'] ?? 'ativo');
$imagemBanner = trim((string) ($_POST['imagem_atual'] ?? ''));

if ($nomeSistema === '' || $tituloPrincipal === '') {
    redirecionarConfigErro('Informe pelo menos o nome do sistema e o titulo principal.');
}

if ($emailContato !== '' && !filter_var($emailContato, FILTER_VALIDATE_EMAIL)) {
    redirecionarConfigErro('Informe um e-mail de contato valido.');
}

if (!in_array($status, ['ativo', 'inativo'], true)) {
    $status = 'ativo';
}

$corPrincipal = corCssSegura($corPrincipal, '#2563eb');
$corSecundaria = corCssSegura($corSecundaria, '#14b8a6');

if (
    $imagemBanner !== '' &&
    strpos($imagemBanner, 'uploads/landing/') !== 0 &&
    strpos($imagemBanner, 'assets/img/') !== 0
) {
    $imagemBanner = '';
}

if (!empty($_FILES['imagem_banner']['name'])) {
    if ($_FILES['imagem_banner']['error'] !== UPLOAD_ERR_OK) {
        redirecionarConfigErro('Nao foi possivel receber o arquivo enviado.');
    }

    if ((int) $_FILES['imagem_banner']['size'] > 2 * 1024 * 1024) {
        redirecionarConfigErro('A imagem deve ter no maximo 2 MB.');
    }

    $nomeOriginal = (string) $_FILES['imagem_banner']['name'];
    $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
    $mimesPermitidos = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
    ];

    if (!isset($mimesPermitidos[$extensao])) {
        redirecionarConfigErro('Envie apenas imagens JPG, JPEG, PNG ou WEBP.');
    }

    $tmpArquivo = (string) $_FILES['imagem_banner']['tmp_name'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeDetectado = $finfo ? finfo_file($finfo, $tmpArquivo) : '';

    if ($finfo) {
        finfo_close($finfo);
    }

    if ($mimeDetectado !== $mimesPermitidos[$extensao] || @getimagesize($tmpArquivo) === false) {
        redirecionarConfigErro('O arquivo enviado nao parece ser uma imagem valida.');
    }

    $diretorioUpload = __DIR__ . '/../uploads/landing';

    if (!is_dir($diretorioUpload) && !mkdir($diretorioUpload, 0755, true)) {
        redirecionarConfigErro('Nao foi possivel preparar a pasta de upload.');
    }

    $baseUploads = realpath(__DIR__ . '/../uploads');
    $destinoUploads = realpath($diretorioUpload);

    if ($baseUploads === false || $destinoUploads === false || strpos($destinoUploads, $baseUploads) !== 0) {
        redirecionarConfigErro('Pasta de upload invalida.');
    }

    $novoNome = 'banner_' . date('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.' . $extensao;
    $destino = $destinoUploads . DIRECTORY_SEPARATOR . $novoNome;

    if (!move_uploaded_file($tmpArquivo, $destino)) {
        redirecionarConfigErro('Nao foi possivel salvar a imagem enviada.');
    }

    $imagemBanner = 'uploads/landing/' . $novoNome;
}

try {
    $conexao = obterConexao();

    if ($id > 0) {
        $stmt = $conexao->prepare(
            'UPDATE landing_config
             SET nome_sistema = ?,
                 slogan = ?,
                 titulo_principal = ?,
                 subtitulo = ?,
                 texto_botao_principal = ?,
                 link_botao_principal = ?,
                 texto_botao_secundario = ?,
                 link_botao_secundario = ?,
                 descricao_sobre = ?,
                 problemas_resolvidos = ?,
                 funcionalidades = ?,
                 diferenciais = ?,
                 texto_chamada_final = ?,
                 cor_principal = ?,
                 cor_secundaria = ?,
                 imagem_banner = ?,
                 whatsapp = ?,
                 email_contato = ?,
                 status = ?,
                 atualizado_em = NOW()
             WHERE id = ?'
        );
        $tipos = str_repeat('s', 19) . 'i';
        $stmt->bind_param(
            $tipos,
            $nomeSistema,
            $slogan,
            $tituloPrincipal,
            $subtitulo,
            $textoBotaoPrincipal,
            $linkBotaoPrincipal,
            $textoBotaoSecundario,
            $linkBotaoSecundario,
            $descricaoSobre,
            $problemasResolvidos,
            $funcionalidades,
            $diferenciais,
            $textoChamadaFinal,
            $corPrincipal,
            $corSecundaria,
            $imagemBanner,
            $whatsapp,
            $emailContato,
            $status,
            $id
        );
        $stmt->execute();
    } else {
        $stmt = $conexao->prepare(
            'INSERT INTO landing_config (
                 nome_sistema,
                 slogan,
                 titulo_principal,
                 subtitulo,
                 texto_botao_principal,
                 link_botao_principal,
                 texto_botao_secundario,
                 link_botao_secundario,
                 descricao_sobre,
                 problemas_resolvidos,
                 funcionalidades,
                 diferenciais,
                 texto_chamada_final,
                 cor_principal,
                 cor_secundaria,
                 imagem_banner,
                 whatsapp,
                 email_contato,
                 status,
                 atualizado_em
             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
        );
        $tipos = str_repeat('s', 19);
        $stmt->bind_param(
            $tipos,
            $nomeSistema,
            $slogan,
            $tituloPrincipal,
            $subtitulo,
            $textoBotaoPrincipal,
            $linkBotaoPrincipal,
            $textoBotaoSecundario,
            $linkBotaoSecundario,
            $descricaoSobre,
            $problemasResolvidos,
            $funcionalidades,
            $diferenciais,
            $textoChamadaFinal,
            $corPrincipal,
            $corSecundaria,
            $imagemBanner,
            $whatsapp,
            $emailContato,
            $status
        );
        $stmt->execute();
    }

    $_SESSION['landing_elos_csrf'] = bin2hex(random_bytes(32));

    header('Location: config_landing.php?sucesso=1');
    exit;
} catch (Throwable $erro) {
    redirecionarConfigErro('Nao foi possivel salvar a configuracao.');
}
