<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/master.php';

iniciarAdmin();

function voltarContratoErro(string $mensagem, int $id = 0): void
{
    $destino = $id > 0 ? 'editar_contrato.php?id=' . $id : 'novo_contrato.php';
    $separador = strpos($destino, '?') === false ? '?' : '&';
    header('Location: ' . $destino . $separador . 'erro=' . urlencode($mensagem));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contratos.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$empresaId = (int) ($_POST['empresa_id'] ?? 0);
$planoId = (int) ($_POST['plano_id'] ?? 0);
$dataInicio = dataOuNull((string) ($_POST['data_inicio'] ?? ''));
$dataFim = dataOuNull((string) ($_POST['data_fim'] ?? ''));
$valorContratado = (float) decimalBanco((string) ($_POST['valor_contratado'] ?? '0'));
$status = (string) ($_POST['status'] ?? 'ATIVO');
$formaPagamento = trim((string) ($_POST['forma_pagamento'] ?? ''));
$observacoes = trim((string) ($_POST['observacoes'] ?? ''));
$statusValidos = ['ATIVO', 'SUSPENSO', 'CANCELADO', 'EXPIRADO'];

if ($empresaId <= 0) {
    voltarContratoErro('Selecione uma empresa.', $id);
}

if ($planoId <= 0) {
    voltarContratoErro('Selecione um plano.', $id);
}

if ($dataInicio === null) {
    voltarContratoErro('Informe uma data de inicio valida.', $id);
}

if (!in_array($status, $statusValidos, true)) {
    voltarContratoErro('Status de contrato invalido.', $id);
}

try {
    $conexao = obterConexao();

    $stmtEmpresa = $conexao->prepare('SELECT nome_empresa FROM empresas WHERE id = ? LIMIT 1');
    $stmtEmpresa->bind_param('i', $empresaId);
    $stmtEmpresa->execute();
    $empresa = $stmtEmpresa->get_result()->fetch_assoc();

    if (!$empresa) {
        voltarContratoErro('Empresa informada nao existe.', $id);
    }

    $stmtPlano = $conexao->prepare('SELECT nome_plano FROM planos WHERE id = ? LIMIT 1');
    $stmtPlano->bind_param('i', $planoId);
    $stmtPlano->execute();
    $plano = $stmtPlano->get_result()->fetch_assoc();

    if (!$plano) {
        voltarContratoErro('Plano informado nao existe.', $id);
    }

    if ($id > 0) {
        $stmtExiste = $conexao->prepare('SELECT id FROM contratos WHERE id = ? LIMIT 1');
        $stmtExiste->bind_param('i', $id);
        $stmtExiste->execute();

        if (!$stmtExiste->get_result()->fetch_assoc()) {
            voltarContratoErro('Contrato nao encontrado.', $id);
        }

        $stmt = $conexao->prepare(
            'UPDATE contratos
             SET empresa_id = ?,
                 plano_id = ?,
                 data_inicio = ?,
                 data_fim = ?,
                 valor_contratado = ?,
                 status = ?,
                 forma_pagamento = ?,
                 observacoes = ?
             WHERE id = ?'
        );
        $stmt->bind_param(
            'iissdsssi',
            $empresaId,
            $planoId,
            $dataInicio,
            $dataFim,
            $valorContratado,
            $status,
            $formaPagamento,
            $observacoes,
            $id
        );
        $stmt->execute();

        registrarLogMaster('Contrato editado', 'contratos', $id, 'Empresa: ' . $empresa['nome_empresa'] . ' | Plano: ' . $plano['nome_plano'], $conexao);
    } else {
        $stmt = $conexao->prepare(
            'INSERT INTO contratos (
                 empresa_id,
                 plano_id,
                 data_inicio,
                 data_fim,
                 valor_contratado,
                 status,
                 forma_pagamento,
                 observacoes
             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'iissdsss',
            $empresaId,
            $planoId,
            $dataInicio,
            $dataFim,
            $valorContratado,
            $status,
            $formaPagamento,
            $observacoes
        );
        $stmt->execute();
        $id = (int) $conexao->insert_id;

        registrarLogMaster('Contrato criado', 'contratos', $id, 'Empresa: ' . $empresa['nome_empresa'] . ' | Plano: ' . $plano['nome_plano'], $conexao);
    }

    header('Location: contratos.php?sucesso=1');
    exit;
} catch (Throwable $erro) {
    voltarContratoErro('Nao foi possivel salvar o contrato.', $id);
}
