# SPEC — InvestNews (Especificacao Tecnica)

## 1. Arquitetura Geral

```
┌─────────────────────────────────────────────────────────────────┐
│                        Docker Compose                           │
│                                                                 │
│  ┌─────────┐    ┌──────────┐    ┌──────────┐    ┌───────────┐  │
│  │  nginx   │───▶│ app      │───▶│  mysql   │    │  redis    │  │
│  │  :8000   │    │ php-fpm  │    │  :3306   │    │  :6379    │  │
│  └─────────┘    └──────────┘    └──────────┘    └───────────┘  │
│                       │                          ▲  ▲  ▲       │
│                       │              ┌───────────┘  │  │       │
│                       │              │              │  │       │
│                  ┌──────────┐   ┌──────────┐  ┌─────────┐     │
│                  │ horizon  │   │  reverb  │  │  redis  │     │
│                  │ (worker) │   │  :8080   │  │commander│     │
│                  └──────────┘   └──────────┘  │  :8081  │     │
│                                               └─────────┘     │
└─────────────────────────────────────────────────────────────────┘
```

**Stack:**
- Backend: Laravel 13 / PHP 8.3
- Frontend: React 19 + React Router 7 + Tailwind CSS 4 (via Vite 8)
- Banco: MySQL 8.0
- Cache/Filas/Ticks: Redis 7
- WebSocket: Laravel Reverb
- Queue Manager: Laravel Horizon
- Auth: Laravel Sanctum (modo SPA, cookie-based)

## 2. Modelos de Dados

### 2.1 Diagrama ER

```
┌────────────┐       ┌────────────────┐
│   users    │       │   categories   │
├────────────┤       ├────────────────┤
│ id (PK)    │       │ id (PK)        │
│ name       │       │ name           │
│ email (UQ) │       │ slug (UQ)      │
│ password   │       │ color (nullable)│
│ timestamps │       │ timestamps     │
│            │       └───────┬────────┘
└────────────┘               │ 1:N
                             │
                      ┌──────┴────────┐
                      │     news      │
                      ├───────────────┤
                      │ id (PK)       │
                      │ title (IDX)   │
                      │ slug (UQ)     │
                      │ content       │
                      │ category_id   │──▶ FK categories
                      │ published_at  │
                      │ timestamps    │
                      └───────────────┘

┌────────────────┐    ┌──────────────────────┐
│   contacts     │    │  market_instruments  │
├────────────────┤    ├──────────────────────┤
│ id (PK)        │    │ id (PK)              │
│ name           │    │ symbol (UQ)          │
│ email (IDX)    │    │ display_name         │
│ message        │    │ feed_id (IDX)        │
│ created_at(IDX)│    │ timestamps           │
│ updated_at     │    └──────────────────────┘
└────────────────┘
                      ┌──────────────────────┐
                      │    feed_configs      │
                      ├──────────────────────┤
                      │ id (PK)              │
                      │ feed_id (UQ)         │
                      │ enabled              │
                      │ interval_sec         │
                      │ timestamps           │
                      └──────────────────────┘

                      ┌──────────────────────┐
                      │  market_schedules    │
                      ├──────────────────────┤
                      │ id (PK)              │
                      │ schedule_date (UQ)   │
                      │ open_time            │
                      │ close_time           │
                      │ market_status (enum) │
                      │ reason               │
                      │ description          │
                      │ timezone             │
                      │ is_dst               │
                      │ is_manual            │
                      │ timestamps           │
                      └──────────────────────┘
```

### 2.2 Campos e Validacoes

| Model | Campo | Tipo | Validacao |
|-------|-------|------|-----------|
| News | title | string(255) | required, max:255, slug unico |
| News | content | text | required |
| News | category_id | foreignId | required, exists:categories |
| News | published_at | date | nullable, formato Y-m-d |
| News | slug | string | auto-gerado a partir do titulo |
| Category | name | string(255) | required, unique, max:255 |
| Category | slug | string | auto-gerado a partir do nome |
| Category | color | string(50) | nullable, max:50 (ex.: sky, emerald, amber) |
| Contact | name | string(120) | required, max:120 |
| Contact | email | string(255) | required, email, max:255 |
| Contact | message | text | required, min:10, max:5000 |
| MarketInstrument | symbol | string(64) | required, regex alfanumerico, unique |
| MarketInstrument | display_name | string(255) | required |
| MarketInstrument | feed_id | string(32) | default: mt5-forex |
| FeedConfig | feed_id | string(32) | unique |
| FeedConfig | enabled | boolean | default: true |
| FeedConfig | interval_sec | int | default: 1 |

## 3. API — Endpoints

### 3.1 Publicos (sem autenticacao)

| Metodo | Rota | Descricao | Response |
|--------|------|-----------|----------|
| `GET` | `/api/categories` | Lista todas as categorias | `CategoryResource[]` |
| `GET` | `/api/news` | Lista noticias publicadas (paginado) | `NewsResource[]` + meta |
| `GET` | `/api/news/{slug}` | Detalhe de noticia por slug | `NewsResource` |
| `POST` | `/api/contacts` | Envia formulario de contato | `202 Accepted` |
| `POST` | `/api/login` | Autentica usuario admin | `User` + session cookie |

**Query params de `/api/news`:**
- `search` — busca multi-token em titulo, conteudo e categoria
- `category` — filtro por slug da categoria
- `page` — pagina (default: 1)
- `per_page` — itens por pagina (5-50, default: 10)

### 3.2 Mercado (Bearer token)

| Metodo | Rota | Descricao | Auth |
|--------|------|-----------|------|
| `GET` | `/api/market/health` | Health check | Nenhuma |
| `POST` | `/api/market/snapshot` | Ingestao de ticks | Bearer token |
| `GET` | `/api/market/quotes` | Consulta cotacoes | Nenhuma |
| `GET` | `/api/feed/config` | Config do feed (enabled, interval, symbols) | Bearer token |

**Query params de `/api/feed/config`:**
- `feed_id` (obrigatorio) — identificador do feed (ex.: `mt5-forex`, `mt5-b3`)

**Resposta:**
```json
{
  "feed_id": "mt5-forex",
  "enabled": true,
  "interval_sec": 1,
  "symbols": ["XAUUSD", "US500", "..."]
}
```

**Payload de `/api/market/snapshot`:**
```json
{
  "feed_id": "mt5-feed",
  "market": "B3",
  "seq": 42,
  "ticks": [
    {
      "symbol": "PETR4",
      "last": 35.42,
      "bid": 35.40,
      "ask": 35.44,
      "prev_close": 34.90,
      "ts": 1711987200000
    }
  ]
}
```

### 3.3 Admin (auth:sanctum)

| Metodo | Rota | Descricao |
|--------|------|-----------|
| `GET/POST` | `/api/admin/categories` | Listar / Criar categoria |
| `GET/PUT/DELETE` | `/api/admin/categories/{id}` | Ver / Atualizar / Excluir |
| `GET/POST` | `/api/admin/news` | Listar / Criar noticia |
| `GET/PUT/DELETE` | `/api/admin/news/{id}` | Ver / Atualizar / Excluir |
| `GET` | `/api/admin/contacts` | Listar contatos recebidos |
| `GET/POST` | `/api/admin/market-instruments` | Listar / Criar instrumento |
| `GET/PUT/DELETE` | `/api/admin/market-instruments/{id}` | Ver / Atualizar / Excluir |
| `GET` | `/api/admin/debug/cache/news` | Inspecao de cache (debug) |

### 3.4 Auth

| Metodo | Rota | Middleware | Descricao |
|--------|------|-----------|-----------|
| `POST` | `/api/login` | — | Login (email + password) |
| `POST` | `/api/logout` | `auth:sanctum` | Logout (invalida session) |
| `GET` | `/api/user` | `auth:sanctum` | Usuario autenticado atual |

## 4. Camadas da Aplicacao

### 4.1 Padrao de Camadas (Backend)

```
Request
  │
  ▼
FormRequest (validacao)
  │
  ▼
Controller (orquestracao)
  │
  ▼
Service (logica de negocio)
  │
  ▼
Repository (acesso a dados)         Cache (NewsCache)
  │                                    │
  ▼                                    ▼
Eloquent Model + Searchable trait   Redis / Array Store
  │
  ▼
Database (MySQL)
```

**Todas as entidades usam Repository Pattern:**
- `NewsRepositoryInterface` / `EloquentNewsRepository`
- `ContactRepositoryInterface` / `EloquentContactRepository`
- `CategoryRepositoryInterface` / `EloquentCategoryRepository`
- `MarketInstrumentRepositoryInterface` / `EloquentMarketInstrumentRepository`
- `FeedConfigRepositoryInterface` / `EloquentFeedConfigRepository`

**Trait `Searchable`** — busca multi-token reutilizavel, configurada via propriedades `$searchable` e `$searchableRelations` em cada Model (News, Category, MarketInstrument).

### 4.2 Fluxo de Cache (Noticias)

```
GET /api/news?category=acoes&page=1
  │
  ▼
NewsService::publicIndexPayload()
  │
  ▼
NewsCache::rememberPublicListPayload()
  │
  ├─ Calcula version key: news:list:version:acoes → v3
  ├─ Monta cache key: news:list:acoes:v3:p1:pp10:q{hash}
  │
  ├─ Cache HIT → retorna payload imediatamente
  │
  └─ Cache MISS → executa query via Repository
       │
       ├─ Armazena resultado com TTL de 5 min
       └─ Retorna payload
```

**Invalidacao via NewsObserver:**
```
News criada/atualizada/excluida
  │
  ▼
NewsObserver
  │
  ├─ bumpListVersion(categoria)      ← incrementa versao da categoria
  ├─ bumpListVersion(null)           ← incrementa versao global "all"
  └─ bumpShowVersion(slug)           ← incrementa versao do detalhe
```

A proxima requisicao calculara uma nova version key, resultando em cache MISS e dados frescos.

### 4.3 Fluxo de Dados de Mercado (Tempo Real)

```
Script MQL5 (MetaTrader 5)
  │
  │  POST /api/market/snapshot
  │  Authorization: Bearer {MARKET_INGEST_KEY}
  │
  ▼
MarketIngestController::snapshot()
  │
  ├─ Valida Bearer token
  ├─ Parse JSON (com fallbacks para MQL5)
  │
  ▼
Para cada tick:
  ├─ Calcula variacao e variacao %
  ├─ Redis::setex("ticks:{SYMBOL}", 10, json)
  ├─ Redis::sadd("market:symbols", SYMBOL)
  └─ event(MarketTickReceived)
       │
       ▼
  Laravel Reverb (WebSocket)
       │
       ├─ Canal: "market-ticks"
       └─ Canal: "market-ticks.{symbol}"
              │
              ▼
  React (MarketTickerWidget)
       │
       ├─ usePublicChannel('market-ticks')
       ├─ Escuta evento 'market.tick'
       └─ Atualiza state com novos precos
```

### 4.4 Fluxo de Contato (Assincrono)

```
Formulario de contato (React)
  │
  │  POST /api/contacts
  │
  ▼
StoreContactRequest (validacao)
  │
  ▼
PublicContactController::store()
  │
  ├─ Dispatch ProcessContactSubmission job
  └─ Return 202 Accepted (resposta imediata)
       │
       ▼  (assincrono via Redis)
ProcessContactSubmission::handle()
  │
  ├─ Fila: 'contacts'
  ├─ Retries: 3 (backoff: 10s, 30s, 60s)
  ├─ Timeout: 60s
  │
  ▼
ContactService::store()
  │
  ▼
Contact (MySQL)
```

## 5. Frontend — Estrutura React

### 5.1 Arvore de Rotas

```
BrowserRouter
  │
  ├─ / ─────────────────────── Layout ─── Home (noticias + cotacoes)
  ├─ /contato ──────────────── Layout ─── Contact (formulario)
  ├─ /noticias/:slug ───────── Layout ─── NewsDetail
  │
  ├─ /admin/login ──────────── Login (sem layout)
  │
  └─ /admin/* ──────────────── AdminLayout (protegido)
       ├─ /admin/categorias ─── Categories
       ├─ /admin/categorias/nova ─── CategoryForm
       ├─ /admin/categorias/:id/editar ─── CategoryForm
       ├─ /admin/noticias ──── NewsList
       ├─ /admin/noticias/nova ─── NewsForm
       ├─ /admin/noticias/:id/editar ─── NewsForm
       ├─ /admin/contatos ──── ContactsList
       ├─ /admin/ativos ────── MarketInstruments
       ├─ /admin/ativos/novo ── MarketInstrumentForm
       └─ /admin/ativos/:id/editar ── MarketInstrumentForm
```

### 5.2 Estado Global e Hooks

- **AuthContext** (`useAuth`): usuario autenticado, login(), logout(), loading
- **Echo** (`useEcho`, `usePublicChannel`): conexao Reverb para WebSocket
- **usePagination** (`usePagination`): hook reutilizavel para listagens paginadas com busca debounced — usado em Categories, NewsList, ContactsList, MarketInstruments

### 5.3 Design System

CSS variables com prefixo `i10-`:
- `--i10-brand`: cor principal da marca
- `--i10-surface`, `--i10-border`, `--i10-text`: tokens de superficie
- Classes utilitarias: `i10-card`, `i10-input`, `i10-btn-primary`, `i10-muted`

## 6. Infraestrutura Docker

### 6.1 Servicos

| Servico | Imagem | Porta | Funcao |
|---------|--------|-------|--------|
| `app` | php:8.3-fpm (custom) | — | PHP-FPM, processa requisicoes |
| `nginx` | nginx:alpine | 8000 | Proxy reverso, serve assets estaticos |
| `mysql` | mysql:8.0 | 3307 | Banco de dados relacional |
| `redis` | redis:7-alpine | — | Cache, filas, ticks de mercado |
| `horizon` | php:8.3-fpm (custom) | — | Worker de filas (auto-scaling) |
| `reverb` | php:8.3-fpm (custom) | 8080 | WebSocket server |
| `node` | node:22-alpine | — | Build do frontend (one-shot) |
| `redis-commander` | rediscommander | 8081 | UI de inspecao do Redis |

### 6.2 Volumes Persistentes

- `mysql_data` — dados do MySQL
- `redis_data` — dados do Redis

### 6.3 Rede

Todos os servicos compartilham a rede bridge `investnews`.

## 7. Testes

### 7.1 Cobertura Atual

| Suite | Arquivo | Cenarios |
|-------|---------|----------|
| Feature | AuthTest | Login sucesso, login falha |
| Feature | CategoryTest | CRUD, unicidade, slug, auth |
| Feature | PublicNewsTest | Listagem, busca, filtro, detalhe |
| Feature | NewsTest | CRUD admin, validacoes |
| Feature | ContactTest | Validacao, dispatch de job |
| Feature | MarketInstrumentTest | CRUD, busca |
| Feature | FeedConfigTest | Config por feed_id, auth, validacao, feed desabilitado |

### 7.2 Ambiente de Testes

- Banco: SQLite in-memory
- Filas: sync (execucao imediata)
- Cache: array store
- Traits: `RefreshDatabase`

## 8. Seguranca

### 8.1 Autenticacao

- **SPA**: Sanctum com cookies HttpOnly + CSRF token
- **Market API**: Bearer token em header `Authorization`
- **Session**: Regenerada apos login; invalidada no logout

### 8.2 Validacao

- Form Requests separados para cada operacao (Store/Update)
- Unicidade de slug validada via custom closure
- Inputs sanitizados (trim, uppercase para simbolos)

### 8.3 Protecoes Ativas

- CSRF via `sanctum/csrf-cookie`
- Throttle em `POST /api/market/snapshot` (120/min)
- Eloquent parametriza queries automaticamente (previne SQL injection)
- Passwords com Bcrypt (12 rounds)

### 8.4 Pontos de Atencao

- Sem authorization por role (qualquer usuario autenticado = admin)
- Sem rate limiting em `/api/contacts`
- Endpoint de debug de cache acessivel em producao
- `.env` deve ser excluido do versionamento

## 9. Refatoracoes Realizadas (v2)

### 9.1 Arquitetura (concluido)

| Item | Antes | Depois |
|------|-------|--------|
| Repository Pattern | Apenas News e Contact | Todas as entidades (+ Category, MarketInstrument) |
| Busca multi-token | Duplicada em News e MarketInstrument | Trait `Searchable` compartilhada |
| Paginacao (frontend) | Logica repetida em 4 componentes | Hook `usePagination` centralizado |
| Nomes de simbolos | Hardcoded no React (50+ entradas) | Consumidos via API `/api/market/quotes` |
| Cores de categorias | Hardcoded no `Home.jsx` | Campo `color` na tabela `categories` |
| Docker health checks | Ausentes | MySQL, Redis e Nginx com health checks |
| Indice `published_at` | Ausente | Migration adicionada |

### 9.2 Performance (concluido)

| Item | Status |
|------|--------|
| Indice em `news.published_at` | Adicionado via migration |
| `searchTokens` no React | Memoizado com `useMemo` |

### 9.3 Oportunidades Remanescentes

| Item | Sugestao |
|------|----------|
| `PublicCategoryController` sem cache | Cache com TTL curto |
| Frontend sem testes | Adicionar Vitest + React Testing Library |
| Sem Error Boundaries | Proteger rotas com boundary |
| Sem TypeScript | Migrar progressivamente para `.tsx` |
