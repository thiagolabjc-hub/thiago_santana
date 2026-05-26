<?php
declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);

define('DB_HOST', 'localhost');
define('DB_USUARIO', 'root');
define('DB_SENHA', '');
define('DB_NOME', 'landing_elos');
define('APP_MASTER_KEY', 'altere-esta-chave-master-elos-em-producao');
define('APP_SESSION_PATH', __DIR__ . '/../storage/sessions');

function obterConexao(): mysqli
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $conexao = new mysqli(DB_HOST, DB_USUARIO, DB_SENHA, DB_NOME);
    $conexao->set_charset('utf8mb4');

    return $conexao;
}

function iniciarSessaoAdminElos(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    if (!is_dir(APP_SESSION_PATH)) {
        mkdir(APP_SESSION_PATH, 0755, true);
    }

    session_save_path(APP_SESSION_PATH);
    session_name('LANDING_ELOS_ADMIN');
    session_start();
}

function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function landingConfigPadrao(): array
{
    return [
        'id' => 0,
        'nome_sistema' => 'ELOS',
        'slogan' => 'Tecnologia conectada à saúde',
        'titulo_principal' => 'Gestão inteligente para laboratórios, clínicas e operações de saúde',
        'subtitulo' => 'Centralize pendências, autorizações, chamados, glosas, clínicas parceiras e indicadores em uma plataforma moderna, segura e preparada para operação B2B.',
        'texto_botao_principal' => 'Solicitar demonstração',
        'link_botao_principal' => '#demonstracao',
        'texto_botao_secundario' => 'Conhecer funcionalidades',
        'link_botao_secundario' => '#funcionalidades',
        'descricao_sobre' => 'O ELOS conecta equipes, clínicas parceiras, motoboys e gestores em uma experiência digital única. A plataforma organiza fluxos operacionais, reduz retrabalho e oferece visibilidade para decisões mais rápidas.',
        'problemas_resolvidos' => "Pendências espalhadas entre planilhas, e-mails e mensagens\nBaixa rastreabilidade de autorizações e chamados\nDificuldade para acompanhar glosas e indicadores\nProcessos manuais que reduzem produtividade\nFalta de visão integrada entre laboratório, clínica e operação externa",
        'funcionalidades' => "Gestão de Pendências\nAutorizações\nChamados\nPortal da Clínica\nPortal do Motoboy\nGlosas\nRelatórios Gerenciais\nControle de Usuários\nIndicadores Operacionais",
        'diferenciais' => "Plataforma B2B pensada para saúde\nPainel administrativo simples para atualização da landing\nFluxos rastreáveis e orientados por indicadores\nExperiência responsiva para diferentes perfis de usuário\nBase preparada para evoluir com novas integrações",
        'texto_chamada_final' => 'Transforme processos manuais em uma operação digital, rastreável e inteligente.',
        'cor_principal' => '#2563eb',
        'cor_secundaria' => '#14b8a6',
        'imagem_banner' => 'assets/img/elos-icon.png',
        'whatsapp' => '',
        'email_contato' => 'contato@elos.com.br',
        'status' => 'ativo',
        'atualizado_em' => '',
    ];
}

function carregarConfiguracaoPublica(): array
{
    $config = landingConfigPadrao();

    try {
        $conexao = obterConexao();
        $resultado = $conexao->query("SELECT * FROM landing_config WHERE status = 'ativo' ORDER BY id DESC LIMIT 1");
        $linha = $resultado->fetch_assoc();

        if ($linha) {
            foreach ($linha as $chave => $valor) {
                if ($valor !== null && $valor !== '') {
                    $config[$chave] = $valor;
                }
            }
        }
    } catch (Throwable $erro) {
        return $config;
    }

    return $config;
}

function textoParaItens(string $texto): array
{
    $linhas = preg_split('/\R+/', $texto) ?: [];
    $itens = [];

    foreach ($linhas as $linha) {
        $item = trim((string) preg_replace('/^\s*[-*]\s*/', '', $linha));

        if ($item !== '') {
            $itens[] = $item;
        }
    }

    return $itens;
}

function corCssSegura(string $cor, string $fallback): string
{
    return preg_match('/^#[0-9a-fA-F]{6}$/', $cor) ? $cor : $fallback;
}

function criptografarValor(string $valor): string
{
    if ($valor === '') {
        return '';
    }

    if (!function_exists('openssl_encrypt')) {
        throw new RuntimeException('OpenSSL indisponivel para criptografia.');
    }

    $iv = random_bytes(16);
    $chave = hash('sha256', APP_MASTER_KEY, true);
    $criptografado = openssl_encrypt($valor, 'AES-256-CBC', $chave, OPENSSL_RAW_DATA, $iv);

    if ($criptografado === false) {
        throw new RuntimeException('Nao foi possivel criptografar o valor.');
    }

    return 'enc:v1:' . base64_encode($iv . $criptografado);
}

function descriptografarValor(string $valor): string
{
    if ($valor === '' || strpos($valor, 'enc:v1:') !== 0 || !function_exists('openssl_decrypt')) {
        return '';
    }

    $dados = base64_decode(substr($valor, 7), true);

    if ($dados === false || strlen($dados) <= 16) {
        return '';
    }

    $iv = substr($dados, 0, 16);
    $criptografado = substr($dados, 16);
    $chave = hash('sha256', APP_MASTER_KEY, true);
    $texto = openssl_decrypt($criptografado, 'AES-256-CBC', $chave, OPENSSL_RAW_DATA, $iv);

    return $texto === false ? '' : $texto;
}
