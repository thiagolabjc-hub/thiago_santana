<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/master.php';

iniciarAdmin();

function voltarEmpresaErro(string $mensagem, int $id = 0): void
{
    $destino = $id > 0 ? 'editar_empresa.php?id=' . $id : 'nova_empresa.php';
    $separador = strpos($destino, '?') === false ? '?' : '&';
    header('Location: ' . $destino . $separador . 'erro=' . urlencode($mensagem));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: empresas.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$nomeEmpresa = trim((string) ($_POST['nome_empresa'] ?? ''));
$nomeFantasia = trim((string) ($_POST['nome_fantasia'] ?? ''));
$cnpj = trim((string) ($_POST['cnpj'] ?? ''));
$emailResponsavel = trim((string) ($_POST['email_responsavel'] ?? ''));
$telefoneResponsavel = trim((string) ($_POST['telefone_responsavel'] ?? ''));
$responsavel = trim((string) ($_POST['responsavel'] ?? ''));
$slug = slugSeguro((string) ($_POST['slug'] ?? ''));
$subdominio = trim((string) ($_POST['subdominio'] ?? ''));
$nomeBanco = trim((string) ($_POST['nome_banco'] ?? ''));
$usuarioBanco = trim((string) ($_POST['usuario_banco'] ?? ''));
$senhaBancoNova = (string) ($_POST['senha_banco'] ?? '');
$status = (string) ($_POST['status'] ?? 'EM_IMPLANTACAO');
$planoId = trim((string) ($_POST['plano_id'] ?? '')) === '' ? null : (int) $_POST['plano_id'];
$dataInicio = dataOuNull((string) ($_POST['data_inicio'] ?? ''));
$dataExpiracao = dataOuNull((string) ($_POST['data_expiracao'] ?? ''));
$observacoes = trim((string) ($_POST['observacoes'] ?? ''));
$statusValidos = ['ATIVA', 'SUSPENSA', 'CANCELADA', 'EM_IMPLANTACAO'];

if ($nomeEmpresa === '') {
    voltarEmpresaErro('Informe o nome da empresa.', $id);
}

if ($slug === '') {
    voltarEmpresaErro('Informe um slug valido.', $id);
}

if ($emailResponsavel !== '' && !filter_var($emailResponsavel, FILTER_VALIDATE_EMAIL)) {
    voltarEmpresaErro('Informe um e-mail de responsavel valido.', $id);
}

if (!in_array($status, $statusValidos, true)) {
    voltarEmpresaErro('Status de empresa invalido.', $id);
}

try {
    $conexao = obterConexao();

    if ($planoId !== null) {
        $stmtPlano = $conexao->prepare('SELECT id FROM planos WHERE id = ? LIMIT 1');
        $stmtPlano->bind_param('i', $planoId);
        $stmtPlano->execute();

        if (!$stmtPlano->get_result()->fetch_assoc()) {
            voltarEmpresaErro('Plano informado nao existe.', $id);
        }
    }

    $stmtSlug = $conexao->prepare('SELECT id FROM empresas WHERE slug = ? AND id <> ? LIMIT 1');
    $stmtSlug->bind_param('si', $slug, $id);
    $stmtSlug->execute();

    if ($stmtSlug->get_result()->fetch_assoc()) {
        voltarEmpresaErro('Ja existe uma empresa com este slug.', $id);
    }

    $senhaBanco = '';
    $statusAnterior = null;

    if ($id > 0) {
        $stmtAtual = $conexao->prepare('SELECT senha_banco, status FROM empresas WHERE id = ? LIMIT 1');
        $stmtAtual->bind_param('i', $id);
        $stmtAtual->execute();
        $empresaAtual = $stmtAtual->get_result()->fetch_assoc();

        if (!$empresaAtual) {
            voltarEmpresaErro('Empresa nao encontrada.', $id);
        }

        $senhaBanco = (string) $empresaAtual['senha_banco'];
        $statusAnterior = (string) $empresaAtual['status'];
    }

    if ($senhaBancoNova !== '') {
        $senhaBanco = criptografarValor($senhaBancoNova);
    }

    if ($id > 0) {
        $stmt = $conexao->prepare(
            'UPDATE empresas
             SET nome_empresa = ?,
                 nome_fantasia = ?,
                 cnpj = ?,
                 email_responsavel = ?,
                 telefone_responsavel = ?,
                 responsavel = ?,
                 slug = ?,
                 subdominio = ?,
                 nome_banco = ?,
                 usuario_banco = ?,
                 senha_banco = ?,
                 status = ?,
                 plano_id = ?,
                 data_inicio = ?,
                 data_expiracao = ?,
                 observacoes = ?
             WHERE id = ?'
        );
        $stmt->bind_param(
            'ssssssssssssisssi',
            $nomeEmpresa,
            $nomeFantasia,
            $cnpj,
            $emailResponsavel,
            $telefoneResponsavel,
            $responsavel,
            $slug,
            $subdominio,
            $nomeBanco,
            $usuarioBanco,
            $senhaBanco,
            $status,
            $planoId,
            $dataInicio,
            $dataExpiracao,
            $observacoes,
            $id
        );
        $stmt->execute();

        registrarLogMaster('Empresa editada', 'empresas', $id, 'Empresa: ' . $nomeEmpresa, $conexao);

        if ($statusAnterior !== null && $statusAnterior !== $status) {
            registrarLogMaster('Status de empresa alterado', 'empresas', $id, $statusAnterior . ' -> ' . $status, $conexao);
        }
    } else {
        $stmt = $conexao->prepare(
            'INSERT INTO empresas (
                 nome_empresa,
                 nome_fantasia,
                 cnpj,
                 email_responsavel,
                 telefone_responsavel,
                 responsavel,
                 slug,
                 subdominio,
                 nome_banco,
                 usuario_banco,
                 senha_banco,
                 status,
                 plano_id,
                 data_inicio,
                 data_expiracao,
                 observacoes
             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'ssssssssssssisss',
            $nomeEmpresa,
            $nomeFantasia,
            $cnpj,
            $emailResponsavel,
            $telefoneResponsavel,
            $responsavel,
            $slug,
            $subdominio,
            $nomeBanco,
            $usuarioBanco,
            $senhaBanco,
            $status,
            $planoId,
            $dataInicio,
            $dataExpiracao,
            $observacoes
        );
        $stmt->execute();
        $id = (int) $conexao->insert_id;

        registrarLogMaster('Empresa criada', 'empresas', $id, 'Empresa: ' . $nomeEmpresa, $conexao);
    }

    header('Location: empresas.php?sucesso=1');
    exit;
} catch (Throwable $erro) {
    voltarEmpresaErro('Nao foi possivel salvar a empresa.', $id);
}
