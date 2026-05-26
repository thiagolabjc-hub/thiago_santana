<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/master.php';

iniciarAdmin();

$busca = trim((string) ($_GET['busca'] ?? ''));
$status = (string) ($_GET['status'] ?? '');
$statusValidos = ['ATIVA', 'SUSPENSA', 'CANCELADA', 'EM_IMPLANTACAO'];
$empresas = [];
$erroBanco = '';

try {
    $conexao = obterConexao();
    $sql = 'SELECT e.*, p.nome_plano
            FROM empresas e
            LEFT JOIN planos p ON p.id = e.plano_id
            WHERE 1 = 1';
    $tipos = '';
    $params = [];

    if ($busca !== '') {
        $like = '%' . $busca . '%';
        $sql .= ' AND (e.nome_empresa LIKE ? OR e.slug LIKE ? OR e.cnpj LIKE ? OR e.responsavel LIKE ?)';
        $tipos .= 'ssss';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    if (in_array($status, $statusValidos, true)) {
        $sql .= ' AND e.status = ?';
        $tipos .= 's';
        $params[] = $status;
    }

    $sql .= ' ORDER BY e.criado_em DESC';
    $resultado = selectPreparado($conexao, $sql, $tipos, $params);

    while ($empresa = $resultado->fetch_assoc()) {
        $empresas[] = $empresa;
    }
} catch (Throwable $erro) {
    $erroBanco = 'Nao foi possivel carregar as empresas. Importe database/update_codex_2.sql e database/update_codex_3.sql se ainda nao fez isso.';
}

renderAdminTopo('Empresas');
?>
    <main class="admin-main">
        <div class="container">
            <div class="admin-heading">
                <div>
                    <span class="section-kicker">Empresas</span>
                    <h1>Clientes e contas B2B</h1>
                </div>
                <a class="btn btn-primary" href="nova_empresa.php"><i class="fa-solid fa-building-circle-plus me-2"></i>Nova Empresa</a>
            </div>

            <?php if (!empty($_GET['sucesso'])): ?>
                <div class="alert alert-success" role="alert">Empresa salva com sucesso.</div>
            <?php endif; ?>

            <?php if ($erroBanco): ?>
                <div class="alert alert-warning" role="alert"><?= e($erroBanco); ?></div>
            <?php endif; ?>

            <section class="admin-panel mb-4">
                <form class="row g-3 align-items-end" method="get">
                    <div class="col-lg-7">
                        <label class="form-label" for="busca">Buscar por nome, slug, CNPJ ou responsavel</label>
                        <input class="form-control" id="busca" name="busca" value="<?= e($busca); ?>">
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Todos</option>
                            <?php foreach ($statusValidos as $item): ?>
                                <option value="<?= e($item); ?>" <?= $status === $item ? 'selected' : ''; ?>><?= e($item); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <button class="btn btn-outline-primary w-100" type="submit"><i class="fa-solid fa-magnifying-glass me-2"></i>Filtrar</button>
                    </div>
                </form>
            </section>

            <section class="admin-panel">
                <div class="panel-header">
                    <div>
                        <h2>Empresas cadastradas</h2>
                        <p><?= e(count($empresas)); ?> registro(s) encontrado(s).</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Empresa</th>
                                <th>Responsavel</th>
                                <th>Plano</th>
                                <th>Status</th>
                                <th>Banco</th>
                                <th>Ambiente</th>
                                <th>Periodo</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$empresas): ?>
                                <tr><td colspan="8" class="text-muted">Nenhuma empresa cadastrada.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($empresas as $empresa): ?>
                                <?php $ambienteCriado = (int) ($empresa['ambiente_criado'] ?? 0) === 1; ?>
                                <tr>
                                    <td>
                                        <strong><?= e($empresa['nome_empresa']); ?></strong><br>
                                        <small><?= e($empresa['slug']); ?><?= $empresa['cnpj'] ? ' | ' . e($empresa['cnpj']) : ''; ?></small>
                                    </td>
                                    <td>
                                        <?= e($empresa['responsavel'] ?: 'Nao informado'); ?><br>
                                        <small><?= e($empresa['email_responsavel'] ?: ''); ?></small>
                                    </td>
                                    <td><?= e($empresa['nome_plano'] ?: 'Sem plano'); ?></td>
                                    <td><?= badgeStatus((string) $empresa['status']); ?></td>
                                    <td>
                                        <?= e($empresa['nome_banco'] ?: 'Nao informado'); ?><br>
                                        <small><?= $empresa['senha_banco'] ? 'Senha cadastrada' : 'Sem senha cadastrada'; ?></small>
                                    </td>
                                    <td>
                                        <?= $ambienteCriado ? badgeStatus('CONCLUIDO') : badgeStatus('PENDENTE'); ?><br>
                                        <small><?= $ambienteCriado && !empty($empresa['data_criacao_ambiente']) ? e(date('d/m/Y H:i', strtotime($empresa['data_criacao_ambiente']))) : 'Aguardando provisionamento'; ?></small>
                                    </td>
                                    <td>
                                        <?= $empresa['data_inicio'] ? e(date('d/m/Y', strtotime($empresa['data_inicio']))) : 'Sem inicio'; ?><br>
                                        <small><?= $empresa['data_expiracao'] ? e(date('d/m/Y', strtotime($empresa['data_expiracao']))) : 'Sem expiracao'; ?></small>
                                    </td>
                                    <td class="table-actions">
                                        <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#empresa<?= e($empresa['id']); ?>">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <a class="btn btn-primary btn-sm" href="editar_empresa.php?id=<?= e($empresa['id']); ?>"><i class="fa-solid fa-pen"></i></a>
                                        <?php if ($ambienteCriado): ?>
                                            <a class="btn btn-outline-primary btn-sm" href="ambiente_empresa.php?id=<?= e($empresa['id']); ?>" title="Ver ambiente"><i class="fa-solid fa-server"></i></a>
                                        <?php else: ?>
                                            <a class="btn btn-outline-primary btn-sm" href="provisionar_empresa.php?id=<?= e($empresa['id']); ?>" title="Criar ambiente"><i class="fa-solid fa-circle-plus"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <?php foreach ($empresas as $empresa): ?>
        <div class="modal fade" id="empresa<?= e($empresa['id']); ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title fs-5"><?= e($empresa['nome_empresa']); ?></h2>
                            <small class="text-muted"><?= e($empresa['slug']); ?></small>
                        </div>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6"><strong>Nome fantasia</strong><br><?= e($empresa['nome_fantasia'] ?: 'Nao informado'); ?></div>
                            <div class="col-md-6"><strong>CNPJ</strong><br><?= e($empresa['cnpj'] ?: 'Nao informado'); ?></div>
                            <div class="col-md-6"><strong>Responsavel</strong><br><?= e($empresa['responsavel'] ?: 'Nao informado'); ?></div>
                            <div class="col-md-6"><strong>Contato</strong><br><?= e($empresa['email_responsavel'] ?: ''); ?> <?= e($empresa['telefone_responsavel'] ?: ''); ?></div>
                            <div class="col-md-6"><strong>Subdominio</strong><br><?= e($empresa['subdominio'] ?: 'Nao informado'); ?></div>
                            <div class="col-md-6"><strong>Banco futuro</strong><br><?= e($empresa['nome_banco'] ?: 'Nao informado'); ?></div>
                            <div class="col-md-6"><strong>Ambiente</strong><br><?= (int) ($empresa['ambiente_criado'] ?? 0) === 1 ? 'Criado' : 'Pendente'; ?></div>
                            <div class="col-md-6"><strong>Admin inicial</strong><br><?= e($empresa['admin_cliente_email'] ?? 'Nao definido'); ?></div>
                            <div class="col-12"><strong>Observacoes</strong><br><?= nl2br(e($empresa['observacoes'] ?: 'Sem observacoes.')); ?></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <?php if ((int) ($empresa['ambiente_criado'] ?? 0) === 1): ?>
                            <a class="btn btn-outline-primary" href="ambiente_empresa.php?id=<?= e($empresa['id']); ?>">Ver Ambiente</a>
                        <?php else: ?>
                            <a class="btn btn-outline-primary" href="provisionar_empresa.php?id=<?= e($empresa['id']); ?>">Criar Ambiente</a>
                        <?php endif; ?>
                        <a class="btn btn-primary" href="editar_empresa.php?id=<?= e($empresa['id']); ?>">Editar</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php renderAdminRodape(); ?>
