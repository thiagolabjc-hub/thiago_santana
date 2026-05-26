<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/master.php';

iniciarAdmin();

$id = (int) ($_GET['id'] ?? 0);
$contrato = null;
$empresas = [];
$planos = [];
$erroBanco = '';

try {
    $conexao = obterConexao();
    $empresas = carregarEmpresas($conexao);
    $planos = carregarPlanos($conexao);

    $stmt = $conexao->prepare('SELECT * FROM contratos WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $contrato = $stmt->get_result()->fetch_assoc();
} catch (Throwable $erro) {
    $erroBanco = 'Nao foi possivel carregar o contrato.';
}

renderAdminTopo('Editar Contrato');
?>
    <main class="admin-main">
        <div class="container">
            <div class="admin-heading">
                <div>
                    <span class="section-kicker">Contratos</span>
                    <h1>Editar Contrato</h1>
                </div>
                <a class="btn btn-outline-primary" href="contratos.php"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
            </div>

            <?php if (!empty($_GET['erro'])): ?>
                <div class="alert alert-danger" role="alert"><?= e($_GET['erro']); ?></div>
            <?php endif; ?>
            <?php if ($erroBanco): ?>
                <div class="alert alert-warning" role="alert"><?= e($erroBanco); ?></div>
            <?php endif; ?>

            <?php if (!$contrato): ?>
                <div class="alert alert-warning" role="alert">Contrato nao encontrado.</div>
            <?php else: ?>
                <?php require __DIR__ . '/includes/form_contrato.php'; ?>
            <?php endif; ?>
        </div>
    </main>
<?php renderAdminRodape(); ?>
