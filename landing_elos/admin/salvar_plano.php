<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/master.php';

iniciarAdmin();

function voltarPlanoErro(string $mensagem, int $id = 0): void
{
    $destino = $id > 0 ? 'editar_plano.php?id=' . $id : 'novo_plano.php';
    $separador = strpos($destino, '?') === false ? '?' : '&';
    header('Location: ' . $destino . $separador . 'erro=' . urlencode($mensagem));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: planos.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$nomePlano = trim((string) ($_POST['nome_plano'] ?? ''));
$descricao = trim((string) ($_POST['descricao'] ?? ''));
$valorMensal = (float) decimalBanco((string) ($_POST['valor_mensal'] ?? '0'));
$limiteUsuarios = inteiroOuNull((string) ($_POST['limite_usuarios'] ?? ''));
$limiteChamados = inteiroOuNull((string) ($_POST['limite_chamados'] ?? ''));
$limitePendencias = inteiroOuNull((string) ($_POST['limite_pendencias'] ?? ''));
$permitePortalClinica = isset($_POST['permite_portal_clinica']) ? 1 : 0;
$permitePortalMotoboy = isset($_POST['permite_portal_motoboy']) ? 1 : 0;
$permiteGlosas = isset($_POST['permite_glosas']) ? 1 : 0;
$permiteRelatorios = isset($_POST['permite_relatorios']) ? 1 : 0;
$status = (int) ($_POST['status'] ?? 1) === 1 ? 1 : 0;

if ($nomePlano === '') {
    voltarPlanoErro('Informe o nome do plano.', $id);
}

try {
    $conexao = obterConexao();

    if ($id > 0) {
        $stmtExiste = $conexao->prepare('SELECT id FROM planos WHERE id = ? LIMIT 1');
        $stmtExiste->bind_param('i', $id);
        $stmtExiste->execute();

        if (!$stmtExiste->get_result()->fetch_assoc()) {
            voltarPlanoErro('Plano nao encontrado.', $id);
        }

        $stmt = $conexao->prepare(
            'UPDATE planos
             SET nome_plano = ?,
                 descricao = ?,
                 valor_mensal = ?,
                 limite_usuarios = ?,
                 limite_chamados = ?,
                 limite_pendencias = ?,
                 permite_portal_clinica = ?,
                 permite_portal_motoboy = ?,
                 permite_glosas = ?,
                 permite_relatorios = ?,
                 status = ?
             WHERE id = ?'
        );
        $stmt->bind_param(
            'ssdiiiiiiiii',
            $nomePlano,
            $descricao,
            $valorMensal,
            $limiteUsuarios,
            $limiteChamados,
            $limitePendencias,
            $permitePortalClinica,
            $permitePortalMotoboy,
            $permiteGlosas,
            $permiteRelatorios,
            $status,
            $id
        );
        $stmt->execute();

        registrarLogMaster('Plano editado', 'planos', $id, 'Plano: ' . $nomePlano, $conexao);
    } else {
        $stmt = $conexao->prepare(
            'INSERT INTO planos (
                 nome_plano,
                 descricao,
                 valor_mensal,
                 limite_usuarios,
                 limite_chamados,
                 limite_pendencias,
                 permite_portal_clinica,
                 permite_portal_motoboy,
                 permite_glosas,
                 permite_relatorios,
                 status
             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'ssdiiiiiiii',
            $nomePlano,
            $descricao,
            $valorMensal,
            $limiteUsuarios,
            $limiteChamados,
            $limitePendencias,
            $permitePortalClinica,
            $permitePortalMotoboy,
            $permiteGlosas,
            $permiteRelatorios,
            $status
        );
        $stmt->execute();
        $id = (int) $conexao->insert_id;

        registrarLogMaster('Plano criado', 'planos', $id, 'Plano: ' . $nomePlano, $conexao);
    }

    header('Location: planos.php?sucesso=1');
    exit;
} catch (Throwable $erro) {
    voltarPlanoErro('Nao foi possivel salvar o plano.', $id);
}
