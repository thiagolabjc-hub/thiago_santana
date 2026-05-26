<?php
declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);

define('DB_HOST', 'localhost');
define('DB_USUARIO', 'root');
define('DB_SENHA', '');
define('DB_NOME', 'landing_elos');

function obterConexao(): mysqli
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $conexao = new mysqli(DB_HOST, DB_USUARIO, DB_SENHA, DB_NOME);
    $conexao->set_charset('utf8mb4');

    return $conexao;
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
