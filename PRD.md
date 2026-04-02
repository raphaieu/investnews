# PRD — InvestNews

## 1. Visao Geral do Produto

**InvestNews** e uma plataforma de noticias voltada para investidores, que combina um portal de conteudo editorial com dados de mercado em tempo real. O sistema foi desenvolvido como desafio tecnico para a Investidor10 e entrega uma experiencia full-stack completa: area publica para leitura de noticias, painel administrativo para gestao de conteudo e um canal de dados financeiros ao vivo via WebSocket.

## 2. Problema

Investidores precisam de uma fonte unica e confiavel para acompanhar noticias do mercado financeiro e cotacoes em tempo real, sem alternar entre multiplas plataformas. Editores precisam de uma ferramenta agil para publicar e gerenciar conteudo categorizado com controle de publicacao (draft vs publicado).

## 3. Publico-Alvo

| Persona | Descricao | Necessidades |
|---------|-----------|-------------|
| **Leitor/Investidor** | Pessoa fisica que acompanha o mercado | Noticias categorizadas, busca por tema, cotacoes ao vivo |
| **Editor/Admin** | Profissional de conteudo | CRUD de noticias e categorias, controle de publicacao, visualizacao de contatos |
| **Integrador de mercado** | Script MQL5 / feed externo | Endpoint para enviar snapshots de cotacoes via API autenticada |

## 4. Objetivos e Metricas de Sucesso

| Objetivo | Metrica |
|----------|---------|
| Entrega de conteudo rapido | Tempo de resposta da API publica < 200ms (cache hit) |
| Dados de mercado em tempo real | Latencia WebSocket < 500ms entre ingestao e exibicao |
| Gestao eficiente de conteudo | Admin consegue publicar noticia em < 2 minutos |
| Disponibilidade | Uptime > 99.5% via Docker Compose |

## 5. Funcionalidades

### 5.1 Area Publica

- **Listagem de noticias** com paginacao, busca multi-token (titulo, conteudo, categoria) e filtro por categoria
- **Detalhe de noticia** acessado por slug amigavel para SEO
- **Widget de cotacoes ao vivo** com variacao percentual, nome do ativo e indicador de conexao WebSocket
- **Formulario de contato** com processamento assincrono via fila (Redis + Horizon)

### 5.2 Area Administrativa

- **Autenticacao** via Sanctum SPA (cookie-based, same-origin)
- **CRUD de Categorias** com validacao de unicidade de nome e slug
- **CRUD de Noticias** com editor de conteudo, selecao de categoria, controle de data de publicacao (draft/agendado/publicado)
- **Listagem de Contatos** recebidos pelo formulario publico
- **CRUD de Instrumentos de Mercado** para gerenciar simbolos e nomes de exibicao
- **Dashboard Horizon** para monitoramento de filas

### 5.3 Integracao de Mercado

- **Endpoint de ingestao** (`POST /api/market/snapshot`) autenticado por Bearer token
- **Armazenamento em Redis** com TTL de 10 segundos por tick
- **Broadcasting via Reverb** em canais publicos (`market-ticks`, `market-ticks.{symbol}`)
- **Endpoint de consulta** (`GET /api/market/quotes`) para polling de cotacoes
- **Endpoint de configuracao** (`GET /api/feed/config`) — o script MQL5 consulta antes de iniciar envio, recebendo lista de simbolos, status (ligado/desligado) e intervalo em segundos
- **Multi-feed**: suporte a feeds independentes (ex.: `mt5-forex`, `mt5-b3`) com controle liga/desliga por feed

### 5.4 Infraestrutura

- **Docker Compose** com 7 servicos: app (php-fpm), nginx, mysql, redis, horizon, reverb, node
- **Cache inteligente** com versionamento por categoria para invalidacao granular
- **Filas assincronas** com Laravel Horizon (auto-scaling, retries, backoff)
- **WebSocket** via Laravel Reverb para streaming de dados

## 6. Requisitos Nao-Funcionais

| Requisito | Especificacao |
|-----------|---------------|
| **Performance** | Cache Redis com TTL de 5 min; invalidacao por versao, nao por flush |
| **Seguranca** | Sanctum SPA (CSRF + session), Bearer token para ingestao de mercado |
| **Escalabilidade** | Horizon com auto-balance ate 10 workers em producao |
| **Resiliencia** | Jobs com retry (3x) e backoff exponencial (10s, 30s, 60s) |
| **Observabilidade** | Horizon dashboard, Redis Commander, endpoint de debug de cache |
| **Compatibilidade** | PHP 8.3+, Node 22+, MySQL 8, Redis 7 |
| **Timezone** | America/Sao_Paulo como padrao; MarketSchedule com suporte a DST |

## 7. Restricoes e Premissas

- **Deploy unico**: Frontend React embutido no Laravel via Vite (sem CORS, sem repositorio separado)
- **Sem CDN**: Assets servidos diretamente pelo nginx do Docker
- **Autenticacao simplificada**: Sem registro publico, sem roles/permissions — apenas admin seedado
- **Dados de mercado**: Dependem de feed externo (script MQL5) para ingestao; sem scraping proprio
- **Banco relacional**: MySQL para dados persistentes; Redis apenas para cache, filas e ticks temporarios

## 8. Riscos Identificados

| Risco | Impacto | Mitigacao Sugerida |
|-------|---------|-------------------|
| Qualquer usuario autenticado acessa rotas admin | Alto | Implementar policies/gates com roles |
| Sem rate limiting no formulario de contato | Medio | Adicionar `throttle` middleware |
| Endpoint de debug de cache expoe estrutura interna | Medio | Restringir a `APP_ENV=local` ou remover em producao |
| Ausencia de testes no frontend | Medio | Adicionar Vitest + Testing Library |

## 9. Melhorias Implementadas (v2)

- [x] Health checks no Docker Compose (mysql, redis, nginx)
- [x] Indice em `news.published_at` para queries de listagem publica
- [x] Repository Pattern padronizado para todas as entidades (Category, MarketInstrument, News, Contact)
- [x] Trait `Searchable` compartilhada para busca multi-token (News, Category, MarketInstrument)
- [x] Hook `usePagination` no frontend — elimina duplicacao em 4 paginas admin
- [x] Nomes de simbolos consumidos via API `/api/market/quotes` (removido dicionario hardcoded do React)
- [x] Cores de categorias movidas para campo `color` na tabela `categories` (configuravel via admin)
- [x] Formulario de categorias com seletor de cor

## 10. Roadmap Futuro

### Fase 1 — Seguranca
- [ ] Implementar authorization com Policies
- [ ] Adicionar rate limiting em endpoints publicos
- [ ] Restringir endpoint de debug de cache a `APP_ENV=local`

### Fase 2 — Qualidade
- [ ] Testes de frontend (Vitest + React Testing Library)
- [ ] Error boundaries no React
- [ ] Migrar progressivamente para TypeScript

### Fase 3 — Evolucao
- [ ] Sistema de roles/permissions (admin, editor, leitor)
- [ ] Upload de imagens para noticias
- [ ] Notificacoes push para noticias relevantes
- [ ] Dashboard analitico (views, categorias populares)
