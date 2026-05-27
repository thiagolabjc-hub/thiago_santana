<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/master.php';

iniciarAdmin();

$id = (int) ($_GET['id'] ?? 0);
$empresa = null;
$resultadoTeste = [
    'status' => 'erro',
    'titulo' => 'Teste nao executado',
    'mensagem' => 'Informe uma empresa valida.',
    'banco' => '',
    'charset' => '',
    'erro_tecnico' => '',
];
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

    if (!$empresa) {
        registrarLogMaster('Empresa nao encontrada', 'empresas', null, 'Teste de conexao para ID ' . $id, $conexao);
        $resultadoTeste['mensagem'] = 'Empresa nao encontrada.';
    } else {
        registrarLogMaster('Teste de conexao realizado', 'empresas', (int) $empresa['id'], 'Empresa: ' . $empresa['nome_empresa'], $conexao);

        $statusEmpresa = (string) $empresa['status'];

        if (in_array($statusEmpresa, ['SUSPENSA', 'CANCELADA'], true)) {
            registrarLogMaster('Tentativa de acesso bloqueado', 'empresas', (int) $empresa['id'], 'Status: ' . $statusEmpresa, $conexao);
            $resultadoTeste = [
                'status' => 'bloqueado',
                'titulo' => 'Acesso bloqueado pelo status',
                'mensagem' => 'Empresas suspensas ou canceladas nao permitem conexao dinamica de cliente.',
                'banco' => (string) ($empresa['nome_banco'] ?? ''),
                'charset' => '',
                'erro_tecnico' => '',
            ];
        } elseif (trim((string) $empresa['nome_banco']) === '') {
            registrarLogMaster('Falha de conexao', 'empresas', (int) $empresa['id'], 'Banco nao configurado.', $conexao);
            $resultadoTeste = [
                'status' => 'erro',
                'titulo' => 'Banco nao configurado',
                'mensagem' => 'Informe o nome do banco da empresa antes de testar a conexao.',
                'banco' => '',
                'charset' => '',
                'erro_tecnico' => '',
            ];
        } elseif (!nomeBancoValido((string) $empresa['nome_banco'])) {
            registrarLogMaster('Falha de conexao', 'empresas', (int) $empresa['id'], 'Nome de banco invalido.', $conexao);
            $resultadoTeste = [
                'status' => 'erro',
                'titulo' => 'Nome de banco invalido',
                'mensagem' => 'Use apenas letras, numeros e underline no nome do banco.',
                'banco' => (string) $empresa['nome_banco'],
                'charset' => '',
                'erro_tecnico' => '',
            ];
        } else {
            try {
                $usuarioBanco = trim((string) ($empresa['usuario_banco'] ?? ''));
                $senhaBancoCriptografada = trim((string) ($empresa['senha_banco'] ?? ''));
                $senhaBanco = $senhaBancoCriptografada !== '' ? descriptografarValor($senhaBancoCriptografada) : '';

                if ($usuarioBanco === '') {
                    $usuarioBanco = DB_USUARIO;
                    $senhaBanco = DB_SENHA;
                }

                $connTeste = mysqli_init();
                $connTeste->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
                $connTeste->real_connect(DB_HOST, $usuarioBanco, $senhaBanco, (string) $empresa['nome_banco']);
                $connTeste->set_charset('utf8mb4');
                $resultadoBanco = $connTeste->query('SELECT DATABASE() AS banco_atual');
                $linhaBanco = $resultadoBanco->fetch_assoc();

                registrarLogMaster('Conexao bem-sucedida', 'empresas', (int) $empresa['id'], 'Banco: ' . $empresa['nome_banco'], $conexao);
                $resultadoTeste = [
                    'status' => 'ok',
                    'titulo' => 'Conexao realizada com sucesso',
                    'mensagem' => 'O painel conseguiu conectar ao banco individual da empresa.',
                    'banco' => (string) ($linhaBanco['banco_atual'] ?? $empresa['nome_banco']),
                    'charset' => $connTeste->character_set_name(),
                    'erro_tecnico' => '',
                ];
            } catch (Throwable $erroConexao) {
                registrarLogMaster('Falha de conexao', 'empresas', (int) $empresa['id'], 'Falha ao conectar no banco informado.', $conexao);
                $resultadoTeste = [
                    'status' => 'erro',
                    'titulo' => 'Falha de conexao',
                    'mensagem' => 'Nao foi possivel conectar ao banco informado.',
                    'banco' => (string) $empresa['nome_banco'],
                    'charset' => '',
                    'erro_tecnico' => substr($erroConexao->getMessage(), 0, 220),
                ];
            }
        }
    }
} catch (Throwable $erro) {
    $erroBanco = 'Nao foi possivel executar o teste de conexao.';
}

renderAdminTopo('Testar Conexao');
?>
    <main class="admin-main">
        <div class="container">
            <div class="admin-heading">
                <div>
                    <span class="section-kicker">Conexao dinamica</span>
                    <h1>Testar Conexao da Empresa</h1>
                </div>
                <a class="btn btn-outline-primary" href="empresas.php"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
            </div>

            <?php if ($erroBanco): ?>
                <div class="alert alert-warning" role="alert"><?= e($erroBanco); ?></div>
            <?php endif; ?>

            <?php if ($empresa): ?>
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
                                <div><span>Banco</span><strong><?= e($empresa['nome_banco'] ?: 'Nao informado'); ?></strong></div>
                                <div>
                                    <span>Credencial de banco</span>
                                    <strong><?= trim((string) $empresa['usuario_banco']) !== '' ? 'Usuario dedicado configurado' : 'Credencial padrao da aplicacao'; ?></strong>
                                </div>
                                <div><span>Plano</span><strong><?= e($empresa['nome_plano'] ?: 'Sem plano'); ?></strong></div>
                            </div>
                        </section>
                    </div>
                    <div class="col-lg-7">
                        <section class="admin-panel h-100">
                            <div class="connection-result connection-<?= e($resultadoTeste['status']); ?>">
                                <i class="fa-solid <?= $resultadoTeste['status'] === 'ok' ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?>"></i>
                                <div>
                                    <h2><?= e($resultadoTeste['titulo']); ?></h2>
                                    <p><?= e($resultadoTeste['mensagem']); ?></p>
                                </div>
                            </div>
                            <div class="summary-list mt-3">
                                <div><span>Banco conectado</span><strong><?= e($resultadoTeste['banco'] ?: 'Nao conectado'); ?></strong></div>
                                <div><span>Charset</span><strong><?= e($resultadoTeste['charset'] ?: 'Nao disponivel'); ?></strong></div>
                            </div>
                            <?php if ($resultadoTeste['erro_tecnico']): ?>
                                <div class="alert alert-light border mt-3 mb-0" role="alert">
                                    Erro tecnico resumido: <?= e($resultadoTeste['erro_tecnico']); ?>
                                </div>
                            <?php endif; ?>
                        </section>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning" role="alert"><?= e($resultadoTeste['mensagem']); ?></div>
            <?php endif; ?>
        </div>
    </main>
<?php renderAdminRodape(); ?>
