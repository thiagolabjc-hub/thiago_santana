# AGENTS.md

Instrucoes para agentes que forem trabalhar neste repositorio.

## Contexto

Este repositorio ainda esta no inicio. Use o `README.md` como referencia principal para entender o objetivo do projeto conforme ele evoluir.

## Diretrizes gerais

- Mantenha as alteracoes pequenas, claras e relacionadas ao pedido.
- Antes de editar, confira a estrutura atual do projeto.
- Nao remova arquivos ou alteracoes existentes sem uma solicitacao explicita.
- Prefira seguir os padroes ja presentes no repositorio.
- Atualize o `README.md` quando uma mudanca alterar instalacao, execucao, estrutura ou uso do projeto.

## Estilo de edicao

- Use nomes de arquivos e pastas claros.
- Mantenha documentacao em Markdown simples.
- Evite comentarios desnecessarios em codigo.
- Use texto em ASCII quando possivel, para evitar problemas de codificacao no ambiente local.

## Verificacao

Quando houver codigo no projeto, execute os comandos de teste, lint ou formatacao disponiveis antes de finalizar uma alteracao.

Se ainda nao houver comandos definidos, registre no resumo final que nenhuma verificacao automatizada foi executada.

## Provisionamento ELOS

- O projeto `landing_elos` possui provisionamento de bancos por cliente a partir do painel master.
- O provisionamento cria apenas a base minima do cliente, usando `templates/cliente_base/install_cliente_base.sql`.
- Nao copiar, importar ou depender do SGP_AUTOCAD durante o provisionamento.
- Nao criar subdominios, DNS, deploy automatico ou migracoes de sistemas de cliente nesta etapa.
- Nunca salvar senhas temporarias em texto puro.

## Git

- Verifique `git status --short` antes e depois das alteracoes.
- Nao reverta mudancas de outros autores.
- Faca commits apenas quando isso for solicitado.
