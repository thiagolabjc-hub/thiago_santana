<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/master.php';

iniciarAdmin();

$id = (int) ($_GET['id'] ?? 0);
$plano = null;
$erroBanco = '';

try {
    $conexao = obterConexao();
    $stmt = $conexao->prepare('SELECT * FROM planos WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $plano = $stmt->get_result()->fetch_assoc();
} catch (Throwable $erro) {
    $erroBanco = 'Nao foi possivel carregar o plano.';
}

renderAdminTopo('Editar Plano');
?>
    <main class="admin-main">
        <div class="container">
            <div class="admin-heading">
                <div>
                    <span class="section-kicker">Planos</span>
                    <h1>Editar Plano</h1>
                </div>
                <a class="btn btn-outline-primary" href="planos.php"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
            </div>

            <?php if (!empty($_GET['erro'])): ?>
                <div class="alert alert-danger" role="alert"><?= e($_GET['erro']); ?></div>
            <?php endif; ?>
            <?php if ($erroBanco): ?>
                <div class="alert alert-warning" role="alert"><?= e($erroBanco); ?></div>
            <?php endif; ?>

            <?php if (!$plano): ?>
                <div class="alert alert-warning" role="alert">Plano nao encontrado.</div>
            <?php else: ?>
                <?php require __DIR__ . '/includes/form_plano.php'; ?>
            <?php endif; ?>
        </div>
    </main>
<?php renderAdminRodape(); ?>
