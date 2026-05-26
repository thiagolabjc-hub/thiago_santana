<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/master.php';

iniciarAdmin();

$id = (int) ($_GET['id'] ?? 0);
$empresa = null;
$planos = [];
$erroBanco = '';

try {
    $conexao = obterConexao();
    $planos = carregarPlanos($conexao);

    $stmt = $conexao->prepare('SELECT * FROM empresas WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $empresa = $stmt->get_result()->fetch_assoc();
} catch (Throwable $erro) {
    $erroBanco = 'Nao foi possivel carregar a empresa.';
}

renderAdminTopo('Editar Empresa');
?>
    <main class="admin-main">
        <div class="container">
            <div class="admin-heading">
                <div>
                    <span class="section-kicker">Empresas</span>
                    <h1>Editar Empresa</h1>
                </div>
                <a class="btn btn-outline-primary" href="empresas.php"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
            </div>

            <?php if (!empty($_GET['erro'])): ?>
                <div class="alert alert-danger" role="alert"><?= e($_GET['erro']); ?></div>
            <?php endif; ?>
            <?php if ($erroBanco): ?>
                <div class="alert alert-warning" role="alert"><?= e($erroBanco); ?></div>
            <?php endif; ?>

            <?php if (!$empresa): ?>
                <div class="alert alert-warning" role="alert">Empresa nao encontrada.</div>
            <?php else: ?>
                <?php require __DIR__ . '/includes/form_empresa.php'; ?>
            <?php endif; ?>
        </div>
    </main>
<?php renderAdminRodape(); ?>
