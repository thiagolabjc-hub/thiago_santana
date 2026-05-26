<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/conexao.php';

function iniciarAdmin(): void
{
    iniciarSessaoAdminElos();

    if (empty($_SESSION['landing_elos_admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function usuarioAdminId(): ?int
{
    return empty($_SESSION['landing_elos_admin_id']) ? null : (int) $_SESSION['landing_elos_admin_id'];
}

function paginaAtualAdmin(): string
{
    return basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
}

function itemMenuAdmin(string $arquivo, string $icone, string $texto): string
{
    $ativo = paginaAtualAdmin() === $arquivo ? ' active' : '';

    return '<a class="admin-menu-link' . $ativo . '" href="' . e($arquivo) . '"><i class="fa-solid ' . e($icone) . '"></i><span>' . e($texto) . '</span></a>';
}

function renderAdminTopo(string $tituloPagina): void
{
    ?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($tituloPagina); ?> | ELOS Master</title>
    <link rel="icon" type="image/png" href="../assets/img/elos-favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="admin-page">
    <nav class="admin-topbar">
        <div class="container">
            <div class="admin-topbar-row">
                <a class="admin-logo" href="dashboard.php">
                    <span class="brand-mark"><img src="../assets/img/elos-favicon.png" alt=""></span>
                    <span>ELOS Master</span>
                </a>
                <div class="admin-user">
                    <span><?= e($_SESSION['landing_elos_admin_nome'] ?? 'Administrador'); ?></span>
                    <a class="btn btn-outline-primary btn-sm" href="../index.php" target="_blank" rel="noopener">Ver landing</a>
                    <a class="btn btn-primary btn-sm" href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket me-1"></i>Sair</a>
                </div>
            </div>
            <div class="admin-menu">
                <?= itemMenuAdmin('dashboard.php', 'fa-gauge-high', 'Dashboard'); ?>
                <?= itemMenuAdmin('config_landing.php', 'fa-pen-to-square', 'Landing'); ?>
                <?= itemMenuAdmin('leads.php', 'fa-users', 'Leads'); ?>
                <?= itemMenuAdmin('empresas.php', 'fa-building', 'Empresas'); ?>
                <?= itemMenuAdmin('planos.php', 'fa-layer-group', 'Planos'); ?>
                <?= itemMenuAdmin('contratos.php', 'fa-file-contract', 'Contratos'); ?>
                <?= itemMenuAdmin('logs_master.php', 'fa-clock-rotate-left', 'Logs Master'); ?>
            </div>
        </div>
    </nav>
<?php
}

function renderAdminRodape(): void
{
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
<?php
}

function badgeStatus(string $status, string $tipo = ''): string
{
    $classe = strtolower(str_replace([' ', '_'], '-', $status));
    $extra = $tipo !== '' ? ' status-' . strtolower($tipo) : '';

    return '<span class="status-pill status-' . e($classe) . e($extra) . '">' . e($status) . '</span>';
}

function registrarLogMaster(string $acao, ?string $entidade = null, ?int $entidadeId = null, string $detalhes = '', ?mysqli $conexao = null): void
{
    try {
        $db = $conexao ?: obterConexao();
        $usuarioId = usuarioAdminId();
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'CLI');
        $stmt = $db->prepare(
            'INSERT INTO logs_master (usuario_admin_id, acao, entidade, entidade_id, detalhes, ip)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('ississ', $usuarioId, $acao, $entidade, $entidadeId, $detalhes, $ip);
        $stmt->execute();
    } catch (Throwable $erro) {
        return;
    }
}

function slugSeguro(string $valor): string
{
    $slug = function_exists('iconv') ? iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $valor) : $valor;
    $slug = strtolower($slug ?: $valor);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?: '';
    $slug = trim($slug, '-');

    return substr($slug, 0, 100);
}

function decimalBanco(string $valor): string
{
    $valor = trim($valor);

    if ($valor === '') {
        return '0.00';
    }

    if (strpos($valor, ',') !== false) {
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
    }

    return number_format((float) $valor, 2, '.', '');
}

function inteiroOuNull(string $valor): ?int
{
    $valor = trim($valor);

    return $valor === '' ? null : max(0, (int) $valor);
}

function dataOuNull(string $valor): ?string
{
    $valor = trim($valor);

    if ($valor === '') {
        return null;
    }

    $data = DateTime::createFromFormat('Y-m-d', $valor);

    return $data ? $data->format('Y-m-d') : null;
}

function carregarPlanos(mysqli $conexao, bool $somenteAtivos = false): array
{
    $sql = 'SELECT id, nome_plano, status FROM planos';

    if ($somenteAtivos) {
        $sql .= ' WHERE status = 1';
    }

    $sql .= ' ORDER BY nome_plano ASC';
    $resultado = $conexao->query($sql);
    $planos = [];

    while ($plano = $resultado->fetch_assoc()) {
        $planos[] = $plano;
    }

    return $planos;
}

function carregarEmpresas(mysqli $conexao): array
{
    $resultado = $conexao->query('SELECT id, nome_empresa, slug, status FROM empresas ORDER BY nome_empresa ASC');
    $empresas = [];

    while ($empresa = $resultado->fetch_assoc()) {
        $empresas[] = $empresa;
    }

    return $empresas;
}

function bindParametros(mysqli_stmt $stmt, string $tipos, array &$parametros): void
{
    if ($tipos === '' || !$parametros) {
        return;
    }

    $referencias = [$tipos];

    foreach ($parametros as $indice => $valor) {
        $referencias[] = &$parametros[$indice];
    }

    call_user_func_array([$stmt, 'bind_param'], $referencias);
}

function selectPreparado(mysqli $conexao, string $sql, string $tipos = '', array $parametros = []): mysqli_result
{
    $stmt = $conexao->prepare($sql);
    bindParametros($stmt, $tipos, $parametros);
    $stmt->execute();

    return $stmt->get_result();
}
