<?php
declare(strict_types=1);

require_once __DIR__ . '/conexao.php';

function obterConexaoMaster(): mysqli
{
    return obterConexao();
}

function registrarLogSistemaMaster(string $acao, ?string $entidade = null, ?int $entidadeId = null, string $detalhes = '', ?mysqli $conexao = null): void
{
    try {
        $db = $conexao ?: obterConexaoMaster();
        $usuarioId = null;
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
