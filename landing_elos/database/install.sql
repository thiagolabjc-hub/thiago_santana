CREATE DATABASE IF NOT EXISTS landing_elos
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE landing_elos;

CREATE TABLE IF NOT EXISTS usuarios_admin (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nivel_acesso ENUM('admin', 'editor') NOT NULL DEFAULT 'admin',
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ultimo_acesso DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS landing_config (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome_sistema VARCHAR(120) NOT NULL,
    slogan VARCHAR(180) NULL,
    titulo_principal VARCHAR(220) NOT NULL,
    subtitulo TEXT NULL,
    texto_botao_principal VARCHAR(80) NULL,
    link_botao_principal VARCHAR(255) NULL,
    texto_botao_secundario VARCHAR(80) NULL,
    link_botao_secundario VARCHAR(255) NULL,
    descricao_sobre TEXT NULL,
    problemas_resolvidos TEXT NULL,
    funcionalidades TEXT NULL,
    diferenciais TEXT NULL,
    texto_chamada_final TEXT NULL,
    cor_principal VARCHAR(20) NOT NULL DEFAULT '#2563eb',
    cor_secundaria VARCHAR(20) NOT NULL DEFAULT '#14b8a6',
    imagem_banner VARCHAR(255) NULL,
    whatsapp VARCHAR(40) NULL,
    email_contato VARCHAR(160) NULL,
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS leads (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(140) NOT NULL,
    empresa VARCHAR(160) NOT NULL,
    email VARCHAR(160) NOT NULL,
    telefone VARCHAR(40) NOT NULL,
    mensagem TEXT NOT NULL,
    origem VARCHAR(80) NOT NULL DEFAULT 'landing_page',
    status ENUM('novo', 'em_contato', 'convertido', 'descartado') NOT NULL DEFAULT 'novo',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_leads_email (email),
    INDEX idx_leads_status (status),
    INDEX idx_leads_criado_em (criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Usuario administrador padrao.
-- E-mail: admin@elos.com.br
-- Senha: Admin@123
-- Hash gerado com password_hash('Admin@123', PASSWORD_DEFAULT), compativel com password_verify.
-- Altere esta senha apos o primeiro acesso.
INSERT INTO usuarios_admin (nome, email, senha, nivel_acesso, status)
VALUES (
    'Administrador ELOS',
    'admin@elos.com.br',
    '$2y$10$LvsVqki3LmcipErbwC9kGu07nkrgvxGCz62K5n8Q0bV3NvJMFVkty',
    'admin',
    'ativo'
)
ON DUPLICATE KEY UPDATE
    nome = VALUES(nome),
    nivel_acesso = VALUES(nivel_acesso),
    status = VALUES(status);

INSERT INTO landing_config (
    nome_sistema,
    slogan,
    titulo_principal,
    subtitulo,
    texto_botao_principal,
    link_botao_principal,
    texto_botao_secundario,
    link_botao_secundario,
    descricao_sobre,
    problemas_resolvidos,
    funcionalidades,
    diferenciais,
    texto_chamada_final,
    cor_principal,
    cor_secundaria,
    imagem_banner,
    whatsapp,
    email_contato,
    status
)
SELECT
    'ELOS',
    'Tecnologia conectada à saúde',
    'Gestão inteligente para laboratórios, clínicas e operações de saúde',
    'Centralize pendências, autorizações, chamados, glosas, clínicas parceiras e indicadores em uma plataforma moderna, segura e preparada para operação B2B.',
    'Solicitar demonstração',
    '#demonstracao',
    'Conhecer funcionalidades',
    '#funcionalidades',
    'O ELOS conecta equipes, clínicas parceiras, motoboys e gestores em uma experiência digital única. A plataforma organiza fluxos operacionais, reduz retrabalho e oferece visibilidade para decisões mais rápidas.',
    'Pendências espalhadas entre planilhas, e-mails e mensagens\nBaixa rastreabilidade de autorizações e chamados\nDificuldade para acompanhar glosas e indicadores\nProcessos manuais que reduzem produtividade\nFalta de visão integrada entre laboratório, clínica e operação externa',
    'Gestão de Pendências\nAutorizações\nChamados\nPortal da Clínica\nPortal do Motoboy\nGlosas\nRelatórios Gerenciais\nControle de Usuários\nIndicadores Operacionais',
    'Plataforma B2B pensada para saúde\nPainel administrativo simples para atualização da landing\nFluxos rastreáveis e orientados por indicadores\nExperiência responsiva para diferentes perfis de usuário\nBase preparada para evoluir com novas integrações',
    'Transforme processos manuais em uma operação digital, rastreável e inteligente.',
    '#2563eb',
    '#14b8a6',
    'assets/img/elos-icon.png',
    '',
    'contato@elos.com.br',
    'ativo'
WHERE NOT EXISTS (SELECT 1 FROM landing_config LIMIT 1);
