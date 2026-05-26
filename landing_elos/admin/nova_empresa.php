<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/master.php';

iniciarAdmin();

$empresa = [
    'id' => 0,
    'nome_empresa' => '',
    'nome_fantasia' => '',
    'cnpj' => '',
    'email_responsavel' => '',
    'telefone_responsavel' => '',
    'responsavel' => '',
    'slug' => '',
    'subdominio' => '',
    'nome_banco' => '',
    'usuario_banco' => '',
    'senha_banco' => '',
    'status' => 'EM_IMPLANTACAO',
    'plano_id' => '',
    'data_inicio' => '',
    'data_expiracao' => '',
    'observacoes' => '',
];
$planos = [];
$erroBanco = '';

try {
    $conexao = obterConexao();
    $planos = carregarPlanos($conexao);
} catch (Throwable $erro) {
    $erroBanco = 'Nao foi possivel carregar os planos.';
}

renderAdminTopo('Nova Empresa');
?>
    <main class="admin-main">
        <div class="container">
            <div class="admin-heading">
                <div>
                    <span class="section-kicker">Empresas</span>
                    <h1>Nova Empresa</h1>
                </div>
                <a class="btn btn-outline-primary" href="empresas.php"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
            </div>

            <?php if (!empty($_GET['erro'])): ?>
                <div class="alert alert-danger" role="alert"><?= e($_GET['erro']); ?></div>
            <?php endif; ?>
            <?php if ($erroBanco): ?>
                <div class="alert alert-warning" role="alert"><?= e($erroBanco); ?></div>
            <?php endif; ?>

            <?php require __DIR__ . '/includes/form_empresa.php'; ?>
        </div>
    </main>
<?php renderAdminRodape(); ?>
