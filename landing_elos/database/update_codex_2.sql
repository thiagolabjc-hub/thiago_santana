USE landing_elos;

CREATE TABLE IF NOT EXISTS planos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_plano VARCHAR(120) NOT NULL,
    descricao TEXT,
    valor_mensal DECIMAL(10,2) DEFAULT 0.00,
    limite_usuarios INT DEFAULT NULL,
    limite_chamados INT DEFAULT NULL,
    limite_pendencias INT DEFAULT NULL,
    permite_portal_clinica TINYINT DEFAULT 0,
    permite_portal_motoboy TINYINT DEFAULT 0,
    permite_glosas TINYINT DEFAULT 0,
    permite_relatorios TINYINT DEFAULT 1,
    status TINYINT DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_planos_status (status),
    INDEX idx_planos_nome (nome_plano)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS empresas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_empresa VARCHAR(180) NOT NULL,
    nome_fantasia VARCHAR(180),
    cnpj VARCHAR(30),
    email_responsavel VARCHAR(180),
    telefone_responsavel VARCHAR(50),
    responsavel VARCHAR(150),
    slug VARCHAR(100) UNIQUE NOT NULL,
    subdominio VARCHAR(150),
    nome_banco VARCHAR(150),
    usuario_banco VARCHAR(150),
    senha_banco VARCHAR(255),
    status ENUM('ATIVA','SUSPENSA','CANCELADA','EM_IMPLANTACAO') DEFAULT 'EM_IMPLANTACAO',
    plano_id INT NULL,
    data_inicio DATE NULL,
    data_expiracao DATE NULL,
    observacoes TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_empresas_status (status),
    INDEX idx_empresas_plano_id (plano_id),
    INDEX idx_empresas_cnpj (cnpj),
    INDEX idx_empresas_responsavel (responsavel)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contratos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT NOT NULL,
    plano_id INT NOT NULL,
    data_inicio DATE NOT NULL,
    data_fim DATE NULL,
    valor_contratado DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('ATIVO','SUSPENSO','CANCELADO','EXPIRADO') DEFAULT 'ATIVO',
    forma_pagamento VARCHAR(100),
    observacoes TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_contratos_empresa_id (empresa_id),
    INDEX idx_contratos_plano_id (plano_id),
    INDEX idx_contratos_status (status),
    INDEX idx_contratos_periodo (data_inicio, data_fim)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS logs_master (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_admin_id INT NULL,
    acao VARCHAR(180) NOT NULL,
    entidade VARCHAR(100),
    entidade_id INT NULL,
    detalhes TEXT,
    ip VARCHAR(60),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_logs_usuario_admin_id (usuario_admin_id),
    INDEX idx_logs_entidade (entidade, entidade_id),
    INDEX idx_logs_criado_em (criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- As tabelas foram criadas sem FOREIGN KEY para manter compatibilidade com ambientes MySQL/MariaDB variados.
-- Quando o servidor estiver validado com InnoDB e migrations controladas, as constraints podem ser adicionadas:
-- ALTER TABLE empresas ADD CONSTRAINT fk_empresas_plano FOREIGN KEY (plano_id) REFERENCES planos(id);
-- ALTER TABLE contratos ADD CONSTRAINT fk_contratos_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id);
-- ALTER TABLE contratos ADD CONSTRAINT fk_contratos_plano FOREIGN KEY (plano_id) REFERENCES planos(id);

INSERT INTO planos (
    nome_plano,
    descricao,
    valor_mensal,
    limite_usuarios,
    limite_chamados,
    limite_pendencias,
    permite_portal_clinica,
    permite_portal_motoboy,
    permite_glosas,
    permite_relatorios,
    status
)
SELECT
    'Básico',
    'Plano inicial para operações que precisam organizar pendências, chamados e relatórios essenciais.',
    0.00,
    10,
    NULL,
    NULL,
    0,
    0,
    0,
    1,
    1
WHERE NOT EXISTS (SELECT 1 FROM planos WHERE nome_plano = 'Básico');

INSERT INTO planos (
    nome_plano,
    descricao,
    valor_mensal,
    limite_usuarios,
    limite_chamados,
    limite_pendencias,
    permite_portal_clinica,
    permite_portal_motoboy,
    permite_glosas,
    permite_relatorios,
    status
)
SELECT
    'Profissional',
    'Plano para empresas que precisam ampliar controle com glosas, portal da clínica e relatórios gerenciais.',
    0.00,
    50,
    NULL,
    NULL,
    1,
    0,
    1,
    1,
    1
WHERE NOT EXISTS (SELECT 1 FROM planos WHERE nome_plano = 'Profissional');

INSERT INTO planos (
    nome_plano,
    descricao,
    valor_mensal,
    limite_usuarios,
    limite_chamados,
    limite_pendencias,
    permite_portal_clinica,
    permite_portal_motoboy,
    permite_glosas,
    permite_relatorios,
    status
)
SELECT
    'Enterprise',
    'Plano completo para operações B2B com portais, glosas, relatórios avançados e expansão comercial.',
    0.00,
    NULL,
    NULL,
    NULL,
    1,
    1,
    1,
    1,
    1
WHERE NOT EXISTS (SELECT 1 FROM planos WHERE nome_plano = 'Enterprise');
