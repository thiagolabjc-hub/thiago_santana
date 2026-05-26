<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/master.php';

iniciarAdmin();

function redirecionarProvisionamentoErro(int $empresaId, string $mensagem): void
{
    $destino = $empresaId > 0 ? 'provisionar_empresa.php?id=' . $empresaId : 'empresas.php';
    $separador = strpos($destino, '?') === false ? '?' : '&';
    header('Location: ' . $destino . $separador . 'erro=' . urlencode($mensagem));
    exit;
}

function executarTemplateCliente(mysqli $cliente, string $arquivoSql): void
{
    $sql = file_get_contents($arquivoSql);

    if ($sql === false || trim($sql) === '') {
        throw new RuntimeException('Template SQL indisponivel.');
    }

    if (!$cliente->multi_query($sql)) {
        throw new RuntimeException('Falha ao executar template.');
    }

    do {
        $resultado = $cliente->store_result();

        if ($resultado instanceof mysqli_result) {
            $resultado->free();
        }

        if (!$cliente->more_results()) {
            break;
        }
    } while ($cliente->next_result());

    if ($cliente->errno) {
        throw new RuntimeException('Falha ao concluir template.');
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: empresas.php');
    exit;
}

$empresaId = (int) ($_POST['empresa_id'] ?? 0);
$adminNome = trim((string) ($_POST['admin_cliente_nome'] ?? ''));
$adminEmail = trim((string) ($_POST['admin_cliente_email'] ?? ''));
$senhaTemporaria = (string) ($_POST['admin_cliente_senha'] ?? '');
$etapaAtual = 'Validacao';

if ($empresaId <= 0) {
    redirecionarProvisionamentoErro(0, 'Empresa invalida.');
}

if ($adminNome === '' || $adminEmail === '' || $senhaTemporaria === '') {
    redirecionarProvisionamentoErro($empresaId, 'Informe nome, e-mail e senha temporaria do admin inicial.');
}

if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
    redirecionarProvisionamentoErro($empresaId, 'Informe um e-mail valido para o admin inicial.');
}

if (strlen($senhaTemporaria) < 8) {
    redirecionarProvisionamentoErro($empresaId, 'A senha temporaria deve ter pelo menos 8 caracteres.');
}

try {
    $central = obterConexao();
    $stmt = $central->prepare(
        'SELECT e.*, p.nome_plano
         FROM empresas e
         LEFT JOIN planos p ON p.id = e.plano_id
         WHERE e.id = ?
         LIMIT 1'
    );
    $stmt->bind_param('i', $empresaId);
    $stmt->execute();
    $empresa = $stmt->get_result()->fetch_assoc();

    if (!$empresa) {
        redirecionarProvisionamentoErro($empresaId, 'Empresa nao encontrada.');
    }

    if ((int) ($empresa['ambiente_criado'] ?? 0) === 1) {
        redirecionarProvisionamentoErro($empresaId, 'Esta empresa ja possui ambiente criado.');
    }

    $slug = (string) $empresa['slug'];
    $nomeBanco = (string) $empresa['nome_banco'];

    if (!slugValidoProvisionamento($slug)) {
        redirecionarProvisionamentoErro($empresaId, 'Slug invalido para provisionamento.');
    }

    if (!nomeBancoValido($nomeBanco)) {
        redirecionarProvisionamentoErro($empresaId, 'Nome do banco invalido para provisionamento.');
    }

    $hashAdmin = password_hash($senhaTemporaria, PASSWORD_DEFAULT);
    $templateSql = __DIR__ . '/../templates/cliente_base/install_cliente_base.sql';
    $identificadorBanco = identificadorBanco($nomeBanco);

    $etapaAtual = 'Inicio';
    registrarProvisionamento($central, $empresaId, 'PROCESSANDO', $etapaAtual, 'Provisionamento iniciado.');
    registrarLogMaster('Inicio do provisionamento', 'empresas', $empresaId, 'Empresa: ' . $empresa['nome_empresa'], $central);

    $stmtInicio = $central->prepare('UPDATE empresas SET ultimo_provisionamento = NOW(), erro_provisionamento = NULL WHERE id = ?');
    $stmtInicio->bind_param('i', $empresaId);
    $stmtInicio->execute();

    $cliente = new mysqli(DB_HOST, DB_USUARIO, DB_SENHA);
    $cliente->set_charset('utf8mb4');

    $etapaAtual = 'Criacao do banco';
    $cliente->query('CREATE DATABASE IF NOT EXISTS ' . $identificadorBanco . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $cliente->select_db($nomeBanco);
    registrarProvisionamento($central, $empresaId, 'PROCESSANDO', $etapaAtual, 'Banco do cliente criado ou validado.');
    registrarLogMaster('Criacao do banco do cliente', 'empresas', $empresaId, 'Banco: ' . $nomeBanco, $central);

    $etapaAtual = 'Execucao do template';
    executarTemplateCliente($cliente, $templateSql);
    registrarProvisionamento($central, $empresaId, 'PROCESSANDO', $etapaAtual, 'Template cliente_base executado.');
    registrarLogMaster('Execucao do template cliente_base', 'empresas', $empresaId, 'Banco: ' . $nomeBanco, $central);

    $etapaAtual = 'Criacao do usuario admin inicial';
    $loginCliente = 'admin';
    $stmtUsuario = $cliente->prepare(
        'INSERT INTO usuariodb (nome, `user`, email, password, nivelAcesso, statusConta)
         VALUES (?, ?, ?, ?, ? , 1)'
    );
    $nivelAcesso = 'admin_empresa';
    $stmtUsuario->bind_param('sssss', $adminNome, $loginCliente, $adminEmail, $hashAdmin, $nivelAcesso);
    $stmtUsuario->execute();
    registrarProvisionamento($central, $empresaId, 'PROCESSANDO', $etapaAtual, 'Admin inicial criado no banco do cliente.');
    registrarLogMaster('Criacao do usuario admin inicial', 'empresas', $empresaId, 'Admin: ' . $adminEmail, $central);

    $etapaAtual = 'Configuracao inicial';
    $nomeEmpresa = (string) $empresa['nome_empresa'];
    $nomePlano = (string) ($empresa['nome_plano'] ?: '');
    $stmtConfig = $cliente->prepare(
        'INSERT INTO configuracoes_cliente (nome_empresa, slug, plano)
         VALUES (?, ?, ?)'
    );
    $stmtConfig->bind_param('sss', $nomeEmpresa, $slug, $nomePlano);
    $stmtConfig->execute();

    $stmtLogCliente = $cliente->prepare(
        'INSERT INTO logs_cliente (usuario, acao, detalhes)
         VALUES (?, ?, ?)'
    );
    $acaoCliente = 'Provisionamento inicial';
    $detalhesCliente = 'Base minima criada pelo painel master ELOS.';
    $stmtLogCliente->bind_param('sss', $adminEmail, $acaoCliente, $detalhesCliente);
    $stmtLogCliente->execute();

    $etapaAtual = 'Conclusao';
    $stmtEmpresa = $central->prepare(
        'UPDATE empresas
         SET ambiente_criado = 1,
             data_criacao_ambiente = NOW(),
             admin_cliente_nome = ?,
             admin_cliente_email = ?,
             admin_cliente_senha_hash = ?,
             erro_provisionamento = NULL,
             ultimo_provisionamento = NOW()
         WHERE id = ?'
    );
    $stmtEmpresa->bind_param('sssi', $adminNome, $adminEmail, $hashAdmin, $empresaId);
    $stmtEmpresa->execute();

    registrarProvisionamento($central, $empresaId, 'CONCLUIDO', $etapaAtual, 'Ambiente criado com sucesso.');
    registrarLogMaster('Conclusao do provisionamento', 'empresas', $empresaId, 'Ambiente criado no banco ' . $nomeBanco, $central);

    header('Location: ambiente_empresa.php?id=' . $empresaId . '&sucesso=1');
    exit;
} catch (Throwable $erro) {
    $mensagemUsuario = 'Nao foi possivel concluir o provisionamento. Verifique os dados e tente novamente.';
    $mensagemInterna = 'Falha na etapa: ' . $etapaAtual;

    try {
        $centralErro = isset($central) && $central instanceof mysqli ? $central : obterConexao();
        $stmtErro = $centralErro->prepare(
            'UPDATE empresas
             SET erro_provisionamento = ?,
                 ultimo_provisionamento = NOW()
             WHERE id = ?'
        );
        $stmtErro->bind_param('si', $mensagemInterna, $empresaId);
        $stmtErro->execute();

        registrarProvisionamento($centralErro, $empresaId, 'ERRO', $etapaAtual, $mensagemInterna);
        registrarLogMaster('Erro no provisionamento', 'empresas', $empresaId, $mensagemInterna, $centralErro);
    } catch (Throwable $erroRegistro) {
        // Falha silenciosa para nao expor detalhes sensiveis ao usuario final.
    }

    redirecionarProvisionamentoErro($empresaId, $mensagemUsuario);
}
