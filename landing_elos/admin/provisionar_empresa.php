<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/master.php';

iniciarAdmin();

$id = (int) ($_GET['id'] ?? 0);
$empresa = null;
$erroBanco = '';

try {
    $conexao = obterConexao();
    $stmt = $conexao->prepare(
        'SELECT e.*, p.nome_plano
         FROM empresas e
         LEFT JOIN planos p ON p.id = e.plano_id
         WHERE e.id = ?
         LIMIT 1'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $empresa = $stmt->get_result()->fetch_assoc();
} catch (Throwable $erro) {
    $erroBanco = 'Nao foi possivel carregar a empresa. Importe database/update_codex_3.sql se ainda nao fez isso.';
}

$ambienteCriado = (int) ($empresa['ambiente_criado'] ?? 0) === 1;
$nomeBanco = (string) ($empresa['nome_banco'] ?? '');
$slug = (string) ($empresa['slug'] ?? '');
$podeProvisionar = $empresa && !$ambienteCriado && nomeBancoValido($nomeBanco) && slugValidoProvisionamento($slug);

renderAdminTopo('Provisionar Ambiente');
?>
    <main class="admin-main">
        <div class="container">
            <div class="admin-heading">
                <div>
                    <span class="section-kicker">Provisionamento</span>
                    <h1>Criar ambiente da empresa</h1>
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
                <div class="row g-4">
                    <div class="col-lg-5">
                        <section class="admin-panel h-100">
                            <div class="panel-header">
                                <div>
                                    <h2><?= e($empresa['nome_empresa']); ?></h2>
                                    <p><?= e($empresa['slug']); ?></p>
                                </div>
                                <?= badgeStatus((string) $empresa['status']); ?>
                            </div>

                            <div class="summary-list">
                                <div><span>Plano</span><strong><?= e($empresa['nome_plano'] ?: 'Sem plano'); ?></strong></div>
                                <div><span>Subdominio</span><strong><?= e($empresa['subdominio'] ?: 'Nao definido'); ?></strong></div>
                                <div><span>Banco planejado</span><strong><?= e($nomeBanco ?: 'Nao informado'); ?></strong></div>
                                <div><span>Ambiente</span><strong><?= $ambienteCriado ? 'Criado' : 'Pendente'; ?></strong></div>
                            </div>

                            <?php if ($ambienteCriado): ?>
                                <div class="alert alert-info mt-3 mb-0" role="alert">
                                    Esta empresa ja possui ambiente criado.
                                    <a href="ambiente_empresa.php?id=<?= e($empresa['id']); ?>">Ver ambiente</a>
                                </div>
                            <?php endif; ?>

                            <?php if (!$ambienteCriado && !nomeBancoValido($nomeBanco)): ?>
                                <div class="alert alert-warning mt-3 mb-0" role="alert">
                                    Informe em Empresas um nome de banco usando apenas letras, numeros e underline.
                                </div>
                            <?php endif; ?>

                            <?php if (!$ambienteCriado && !slugValidoProvisionamento($slug)): ?>
                                <div class="alert alert-warning mt-3 mb-0" role="alert">
                                    O slug deve conter apenas letras minusculas, numeros e hifen.
                                </div>
                            <?php endif; ?>
                        </section>
                    </div>

                    <div class="col-lg-7">
                        <form class="admin-panel" method="post" action="executar_provisionamento.php" novalidate>
                            <input type="hidden" name="empresa_id" value="<?= e($empresa['id']); ?>">

                            <div class="panel-header">
                                <div>
                                    <h2>Admin inicial do cliente</h2>
                                    <p>Este usuario sera criado apenas no banco minimo do cliente.</p>
                                </div>
                            </div>

                            <div class="alert alert-warning" role="alert">
                                A senha temporaria deve ser alterada pelo cliente no primeiro acesso quando o portal operacional for implementado.
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="admin_cliente_nome">Nome do admin inicial</label>
                                    <input class="form-control" id="admin_cliente_nome" name="admin_cliente_nome" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="admin_cliente_email">E-mail do admin inicial</label>
                                    <input class="form-control" id="admin_cliente_email" name="admin_cliente_email" type="email" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="admin_cliente_senha">Senha temporaria</label>
                                    <input class="form-control" id="admin_cliente_senha" name="admin_cliente_senha" type="password" minlength="8" autocomplete="new-password" required>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-primary btn-lg w-100" type="submit" <?= $podeProvisionar ? '' : 'disabled'; ?>>
                                        <i class="fa-solid fa-server me-2"></i>Criar Ambiente
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
<?php renderAdminRodape(); ?>
