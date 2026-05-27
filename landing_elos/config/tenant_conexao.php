<?php
declare(strict_types=1);

require_once __DIR__ . '/tenant_resolver.php';

$connMaster = null;
$connCliente = null;
$empresaAtual = null;

function redirecionarErroTenant(string $codigo, string $mensagem): void
{
    $url = 'erro_empresa.php?codigo=' . urlencode($codigo) . '&mensagem=' . urlencode($mensagem);
    header('Location: ' . $url);
    exit;
}

try {
    $connMaster = obterConexaoMaster();
    $tenantSlug = getTenantSlug();

    if ($tenantSlug === '') {
        registrarLogSistemaMaster('Empresa nao encontrada', 'empresas', null, 'Nenhum slug, subdominio ou parametro local informado.', $connMaster);
        redirecionarErroTenant('empresa_nao_informada', 'Informe uma empresa valida para acessar a area do cliente.');
    }

    $empresaAtual = buscarEmpresaPorSlugOuSubdominio($connMaster, $tenantSlug);
    $validacao = validarEmpresaAcessivel($empresaAtual);

    if (!$validacao['ok']) {
        $empresaId = $empresaAtual ? (int) $empresaAtual['id'] : null;
        $acao = $empresaAtual ? 'Tentativa de acesso bloqueado' : 'Empresa nao encontrada';
        registrarLogSistemaMaster($acao, 'empresas', $empresaId, $validacao['mensagem'], $connMaster);
        redirecionarErroTenant($validacao['codigo'], $validacao['mensagem']);
    }

    $nomeBanco = (string) $empresaAtual['nome_banco'];

    if (!preg_match('/^[A-Za-z0-9_]+$/', $nomeBanco)) {
        registrarLogSistemaMaster('Falha de conexao do cliente', 'empresas', (int) $empresaAtual['id'], 'Nome de banco invalido.', $connMaster);
        redirecionarErroTenant('banco_nao_configurado', 'Configuracao de banco invalida para esta empresa.');
    }

    $usuarioBanco = trim((string) ($empresaAtual['usuario_banco'] ?? ''));
    $senhaBancoCriptografada = trim((string) ($empresaAtual['senha_banco'] ?? ''));
    $senhaBanco = $senhaBancoCriptografada !== '' ? descriptografarValor($senhaBancoCriptografada) : '';

    if ($usuarioBanco === '') {
        $usuarioBanco = DB_USUARIO;
        $senhaBanco = DB_SENHA;
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $connCliente = mysqli_init();
    $connCliente->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
    $connCliente->real_connect(DB_HOST, $usuarioBanco, $senhaBanco, $nomeBanco);
    $connCliente->set_charset('utf8mb4');
} catch (Throwable $erro) {
    if ($empresaAtual) {
        registrarLogSistemaMaster('Falha de conexao do cliente', 'empresas', (int) $empresaAtual['id'], 'Falha ao conectar no banco do cliente.', $connMaster instanceof mysqli ? $connMaster : null);
    }

    redirecionarErroTenant('falha_conexao', 'Nao foi possivel conectar ao ambiente da empresa agora.');
}
