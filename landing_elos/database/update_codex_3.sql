USE landing_elos;

ALTER TABLE empresas
    ADD COLUMN IF NOT EXISTS ambiente_criado TINYINT DEFAULT 0 AFTER observacoes,
    ADD COLUMN IF NOT EXISTS data_criacao_ambiente DATETIME NULL AFTER ambiente_criado,
    ADD COLUMN IF NOT EXISTS admin_cliente_nome VARCHAR(150) NULL AFTER data_criacao_ambiente,
    ADD COLUMN IF NOT EXISTS admin_cliente_email VARCHAR(180) NULL AFTER admin_cliente_nome,
    ADD COLUMN IF NOT EXISTS admin_cliente_senha_hash VARCHAR(255) NULL AFTER admin_cliente_email,
    ADD COLUMN IF NOT EXISTS erro_provisionamento TEXT NULL AFTER admin_cliente_senha_hash,
    ADD COLUMN IF NOT EXISTS ultimo_provisionamento DATETIME NULL AFTER erro_provisionamento;

CREATE TABLE IF NOT EXISTS provisionamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    status ENUM('PENDENTE','PROCESSANDO','CONCLUIDO','ERRO') DEFAULT 'PENDENTE',
    etapa VARCHAR(150),
    mensagem TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_provisionamentos_empresa_id (empresa_id),
    INDEX idx_provisionamentos_status (status),
    INDEX idx_provisionamentos_criado_em (criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A relacao provisionamentos.empresa_id pode receber FOREIGN KEY futuramente:
-- ALTER TABLE provisionamentos ADD CONSTRAINT fk_provisionamentos_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id);
