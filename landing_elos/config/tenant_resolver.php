<?php
declare(strict_types=1);

require_once __DIR__ . '/master_conexao.php';

function getCurrentHost(): string
{
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    $host = preg_replace('/:\d+$/', '', $host) ?: $host;

    return trim($host);
}

function isLocalHostTenant(string $host): bool
{
    return in_array($host, ['localhost', '127.0.0.1'], true) || substr($host, -10) === '.localhost';
}

function slugTenantValido(string $slug): bool
{
    return preg_match('/^[a-z0-9-]+$/', $slug) === 1;
}

function getSubdomain(): string
{
    $host = getCurrentHost();

    if (isLocalHostTenant($host) || filter_var($host, FILTER_VALIDATE_IP)) {
        return '';
    }

    $partes = explode('.', $host);

    if (count($partes) < 3) {
        return '';
    }

    $subdominio = $partes[0];

    if (in_array($subdominio, ['www', 'app'], true)) {
        return '';
    }

    return slugTenantValido($subdominio) ? $subdominio : '';
}

function getSlugFromPath(): string
{
    $path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
    $segmentos = array_values(array_filter(explode('/', trim((string) $path, '/'))));
    $ignorados = [
        'landing_elos',
        'cliente',
        'admin',
        'assets',
        'config',
        'index.php',
        'login.php',
        'erro_empresa.php',
    ];

    foreach ($segmentos as $segmento) {
        $segmento = strtolower($segmento);

        if (in_array($segmento, $ignorados, true)) {
            continue;
        }

        if (slugTenantValido($segmento)) {
            return $segmento;
        }
    }

    return '';
}

function getTenantSlug(): string
{
    $host = getCurrentHost();
    $empresaTeste = strtolower(trim((string) ($_GET['empresa'] ?? '')));

    if (isLocalHostTenant($host) && $empresaTeste !== '' && slugTenantValido($empresaTeste)) {
        return $empresaTeste;
    }

    $subdominio = getSubdomain();

    if ($subdominio !== '') {
        return $subdominio;
    }

    return getSlugFromPath();
}

function buscarEmpresaPorSlugOuSubdominio(mysqli $connMaster, string $slug): ?array
{
    if (!slugTenantValido($slug)) {
        return null;
    }

    $stmt = $connMaster->prepare(
        'SELECT e.*, p.nome_plano
         FROM empresas e
         LEFT JOIN planos p ON p.id = e.plano_id
         WHERE e.slug = ? OR e.subdominio = ?
         LIMIT 1'
    );
    $stmt->bind_param('ss', $slug, $slug);
    $stmt->execute();
    $empresa = $stmt->get_result()->fetch_assoc();

    return $empresa ?: null;
}

function validarEmpresaAcessivel(?array $empresa): array
{
    if (!$empresa) {
        return [
            'ok' => false,
            'codigo' => 'empresa_nao_encontrada',
            'mensagem' => 'Empresa nao encontrada.',
        ];
    }

    $status = (string) ($empresa['status'] ?? '');

    if ($status === 'SUSPENSA') {
        return [
            'ok' => false,
            'codigo' => 'empresa_suspensa',
            'mensagem' => 'Esta empresa esta temporariamente suspensa.',
        ];
    }

    if ($status === 'CANCELADA') {
        return [
            'ok' => false,
            'codigo' => 'empresa_cancelada',
            'mensagem' => 'Esta empresa nao possui acesso ativo.',
        ];
    }

    if (!in_array($status, ['ATIVA', 'EM_IMPLANTACAO'], true)) {
        return [
            'ok' => false,
            'codigo' => 'status_invalido',
            'mensagem' => 'Status da empresa nao permite acesso.',
        ];
    }

    if (trim((string) ($empresa['nome_banco'] ?? '')) === '') {
        return [
            'ok' => false,
            'codigo' => 'banco_nao_configurado',
            'mensagem' => 'O banco da empresa ainda nao foi configurado.',
        ];
    }

    return [
        'ok' => true,
        'codigo' => 'ok',
        'mensagem' => 'Empresa acessivel.',
    ];
}
