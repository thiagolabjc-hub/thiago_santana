<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/master.php';

iniciarAdmin();

$plano = [
    'id' => 0,
    'nome_plano' => '',
    'descricao' => '',
    'valor_mensal' => '0.00',
    'limite_usuarios' => '',
    'limite_chamados' => '',
    'limite_pendencias' => '',
    'permite_portal_clinica' => 0,
    'permite_portal_motoboy' => 0,
    'permite_glosas' => 0,
    'permite_relatorios' => 1,
    'status' => 1,
];

renderAdminTopo('Novo Plano');
?>
    <main class="admin-main">
        <div class="container">
            <div class="admin-heading">
                <div>
                    <span class="section-kicker">Planos</span>
                    <h1>Novo Plano</h1>
                </div>
                <a class="btn btn-outline-primary" href="planos.php"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
            </div>

            <?php if (!empty($_GET['erro'])): ?>
                <div class="alert alert-danger" role="alert"><?= e($_GET['erro']); ?></div>
            <?php endif; ?>

            <?php require __DIR__ . '/includes/form_plano.php'; ?>
        </div>
    </main>
<?php renderAdminRodape(); ?>
