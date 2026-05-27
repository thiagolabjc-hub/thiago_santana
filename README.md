# thiago_santana

Repositorio do projeto `landing_elos`, uma landing page comercial do ELOS com painel master B2B/SaaS.

## Sobre

O projeto `landing_elos` e independente do SGP_AUTOCAD. Ele possui:

- landing page publica para apresentacao comercial e captacao de leads;
- painel administrativo da landing;
- painel master para empresas, planos, contratos e logs;
- provisionamento inicial de banco por empresa;
- camada de conexao dinamica por empresa/banco;
- area de cliente apenas para teste tecnico da conexao.

## Tecnologias

- PHP com `mysqli`
- MySQL
- Bootstrap 5
- Font Awesome
- HTML, CSS e JavaScript sem Composer ou frameworks PHP

## Instalacao

1. Configure o banco master em `landing_elos/config/conexao.php`.
2. Execute `landing_elos/database/install.sql`.
3. Execute `landing_elos/database/update_codex_2.sql`.
4. Execute `landing_elos/database/update_codex_3.sql`.
5. Acesse `landing_elos/admin/login.php`.

Usuario inicial:

```text
email: admin@elos.com.br
senha: Admin@123
```

Altere a senha padrao apos o primeiro acesso.

## Conexao dinamica

A area de cliente resolve a empresa por:

- subdominio, como `cliente1.elos.com.br`;
- primeiro segmento da URL, como `elos.com.br/cliente1`;
- parametro local de teste, como `cliente/index.php?empresa=cliente1`.

O banco master consulta a empresa cadastrada e a area de cliente conecta no banco informado em `empresas.nome_banco`. Status `ATIVA` e `EM_IMPLANTACAO` permitem acesso. Status `SUSPENSA` e `CANCELADA` bloqueiam com tela amigavel.

## Estrutura principal

```text
landing_elos/
+-- admin/
+-- assets/
+-- cliente/
+-- config/
+-- database/
+-- templates/
+-- uploads/
+-- index.php
```

## Seguranca

- Senhas de usuarios usam `password_hash`.
- Operacoes de banco usam prepared statements.
- Saidas HTML devem usar `htmlspecialchars`.
- Credenciais de banco de clientes nao devem ser exibidas em telas.
- O projeto nao deve importar, alterar ou depender do SGP_AUTOCAD.

## Autor

Thiago Santana
