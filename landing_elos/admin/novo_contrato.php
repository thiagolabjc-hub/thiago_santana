<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/master.php';

iniciarAdmin();

$contrato = [
    'id' => 0,
    'empresa_id' => '',
    'plano_id' => '',
    'data_inicio' => date('Y-m-d'),
    'data_fim' => '',
    'valor_contratado' => '0.00',
    'status' => 'ATIVO',
    'forma_pagamento' => '',
    'observacoes' => '',
];
$empresas = [];
$planos = [];
$erroBanco = '';

try {
    $conexao = obterConexao();
    $empresas = carregarEmpresas($conexao);
    $planos = carregarPlanos($conexao);
} catch (Throwable $erro) {
    $erroBanco = 'Nao foi possivel carregar empresas e planos.';
}

renderAdminTopo('Novo Contrato');
?>
    <main class="admin-main">
        <div class="container">
            <div class="admin-heading">
                <div>
                    <span class="section-kicker">Contratos</span>
                    <h1>Novo Contrato</h1>
                </div>
                <a class="btn btn-outline-primary" href="contratos.php"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
            </div>

            <?php if (!empty($_GET['erro'])): ?>
                <div class="alert alert-danger" role="alert"><?= e($_GET['erro']); ?></div>
            <?php endif; ?>
            <?php if ($erroBanco): ?>
                <div class="alert alert-warning" role="alert"><?= e($erroBanco); ?></div>
            <?php endif; ?>

            <?php require __DIR__ . '/includes/form_contrato.php'; ?>
        </div>
    </main>
<?php renderAdminRodape(); ?>
