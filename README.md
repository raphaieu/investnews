# InvestNews

Sistema de gerenciamento e consulta de noticias para investidores com cotacoes de mercado em tempo real, desenvolvido como desafio tecnico para a Investidor10.

## Stack

- **Backend:** Laravel 13 (PHP 8.3) com Sanctum SPA
- **Frontend:** React 19 + Tailwind CSS 4 (embutido via Vite 8)
- **Banco:** MySQL 8
- **Cache/Filas:** Redis 7 + Laravel Horizon
- **WebSocket:** Laravel Reverb (cotacoes ao vivo)
- **Infra:** Docker Compose (nginx + php-fpm + mysql + redis + horizon + reverb)

## Funcionalidades

### Area Publica
- Listagem de noticias com busca multi-token (titulo, conteudo, categoria) e filtro por categoria
- Detalhe de noticia por slug (SEO-friendly)
- Widget de cotacoes ao vivo com variacao percentual e indicador de conexao
- Formulario de contato com processamento assincrono via fila

### Area Admin
- Login via Sanctum SPA (cookie-based)
- CRUD de categorias com validacao de unicidade (nome e slug)
- CRUD de noticias com controle de publicacao (draft, publicado, agendado)
- Visualizacao de contatos recebidos
- CRUD de instrumentos de mercado (simbolos e nomes de exibicao)
- Dashboard Horizon para monitoramento de filas

### Integracao de Mercado
- Endpoint de ingestao de cotacoes via Bearer token (`POST /api/market/snapshot`)
- Broadcasting em tempo real via WebSocket (Laravel Reverb)
- Consulta de cotacoes via API (`GET /api/market/quotes`)

## Arquitetura

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

### Decisoes Tecnicas

- **React via Vite dentro do Laravel** para deploy unico, sem CORS, sem complexidade de dois repositorios
- **Sanctum modo SPA** (cookie-based) para autenticacao — mais simples e seguro que tokens para same-origin
- **Repository Pattern** para todas as entidades — separacao consistente de responsabilidades no acesso a dados
- **Cache com versionamento** — invalidacao granular por categoria sem flush global
- **Filas com Horizon** — processamento assincrono de contatos com retry e backoff exponencial
- **Reverb para WebSocket** — broadcasting nativo do Laravel para cotacoes ao vivo

## Fluxos Principais

### Fluxo de Cache (Noticias)

```
GET /api/news?category=acoes
  │
  ▼
NewsService → NewsCache
  │
  ├─ Busca versao: news:list:version:acoes → v3
  ├─ Monta chave: news:list:acoes:v3:p1:pp10:q{hash}
  │
  ├─ HIT  → retorna payload do Redis
  └─ MISS → query via Repository → armazena (TTL 5min)

Quando noticia e criada/editada/excluida:
  NewsObserver → bumpa versoes → proximo request = cache MISS
```

### Fluxo de Cotacoes (Tempo Real)

```
MetaTrader 5 (MQL5)
  │
  │  1. GET /api/feed/config?feed_id=mt5-forex (Bearer token)
  │     → { enabled, interval_sec, symbols[] }
  │     O script decide se envia ticks e com qual intervalo
  │
  │  2. POST /api/market/snapshot (Bearer token)
  ▼
MarketIngestController
  ├─ Armazena ticks no Redis (TTL 10s)
  └─ Dispara MarketTickReceived
       │
       ▼
  Reverb (WebSocket) → canal "market-ticks"
       │
       ▼
  React (MarketTickerWidget) → atualiza UI
```

Cada feed (mt5-forex, mt5-b3) pode ser ligado/desligado independentemente via tabela `feed_configs`. O campo `interval_sec` controla a frequencia de envio no script MQL5.

### Fluxo de Contato (Assincrono)

```
POST /api/contacts → 202 Accepted (resposta imediata)
  │
  ▼  (Redis queue)
Horizon → ProcessContactSubmission
  ├─ Retries: 3x (backoff: 10s, 30s, 60s)
  └─ ContactService::store() → MySQL
```

## Setup Local com Docker

```bash
# 1. Clonar e configurar
git clone git@github.com:raphaieu/investnews.git
cd investnews
cp .env.example .env

# 2. Subir containers
docker compose up -d

# 3. Instalar dependencias, gerar key, rodar migrations e seeds
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed

# 4. Build do frontend
docker compose run --rm node

# 5. Acessar
# App:              http://localhost:8000
# Redis Commander:  http://localhost:8081
# WebSocket:        ws://localhost:8080
```

## Setup Local sem Docker

```bash
# Requer PHP 8.3+, Composer, Node 22+, MySQL 8, Redis 7

composer install
cp .env.example .env
# Ajustar DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD no .env
# Ajustar REDIS_HOST para 127.0.0.1
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

Para desenvolvimento com hot-reload:
```bash
composer dev
# Inicia: server, queue, logs (pail) e vite em paralelo
```

## Credenciais do Admin (seedado)

| Campo | Valor |
|-------|-------|
| E-mail | admin@investnews.com |
| Senha | password |

## Endpoints da API

### Publicos

| Metodo | Rota | Descricao |
|--------|------|-----------|
| `GET` | `/api/categories` | Lista categorias |
| `GET` | `/api/news` | Lista noticias (search, category, page, per_page) |
| `GET` | `/api/news/{slug}` | Detalhe de noticia |
| `POST` | `/api/contacts` | Formulario de contato (async) |
| `POST` | `/api/login` | Autenticacao |

### Mercado

| Metodo | Rota | Auth | Descricao |
|--------|------|------|-----------|
| `GET` | `/api/market/health` | — | Health check |
| `POST` | `/api/market/snapshot` | Bearer | Ingestao de ticks |
| `GET` | `/api/market/quotes` | — | Consulta cotacoes |
| `GET` | `/api/feed/config?feed_id=` | Bearer | Config do feed (enabled, interval, symbols) |

### Admin (auth:sanctum)

| Metodo | Rota | Descricao |
|--------|------|-----------|
| `CRUD` | `/api/admin/categories` | Categorias |
| `CRUD` | `/api/admin/news` | Noticias |
| `GET` | `/api/admin/contacts` | Contatos |
| `CRUD` | `/api/admin/market-instruments` | Instrumentos |

## Servicos Docker

| Servico | Porta | Descricao |
|---------|-------|-----------|
| `nginx` | 8000 | Proxy reverso + assets estaticos |
| `app` | — | PHP-FPM (processa requisicoes) |
| `mysql` | 3307 | Banco de dados |
| `redis` | — | Cache, filas, ticks |
| `horizon` | — | Worker de filas (auto-scaling) |
| `reverb` | 8080 | WebSocket server |
| `redis-commander` | 8081 | UI do Redis |
| `node` | — | Build do frontend (one-shot) |

## Variaveis de Ambiente Principais

```env
# App
APP_URL=http://localhost:8000
APP_TIMEZONE=America/Sao_Paulo

# Database
DB_HOST=mysql              # 'mysql' para Docker, '127.0.0.1' para local
DB_DATABASE=investnews
DB_USERNAME=investnews
DB_PASSWORD=secret

# Redis
REDIS_HOST=redis           # 'redis' para Docker, '127.0.0.1' para local
NEWS_CACHE_STORE=redis     # Cache de noticias via Redis
NEWS_CACHE_TTL=300         # TTL do cache em segundos

# Filas
QUEUE_CONNECTION=redis

# WebSocket (Reverb)
REVERB_APP_KEY=            # Gerar valor unico
REVERB_APP_SECRET=         # Gerar valor unico
REVERB_HOST=0.0.0.0
REVERB_PORT=8080

# Market API
MARKET_INGEST_KEY=         # Bearer token para ingestao de cotacoes

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost:8000,localhost:5173

# Horizon
HORIZON_ALLOWED_EMAILS=    # Emails permitidos em producao
```

## Executar Testes

```bash
php artisan test
# ou
docker compose exec app php artisan test
```

Os testes cobrem:
- Login de admin (sucesso e falha)
- CRUD de categorias (criacao, validacoes, unicidade de nome e slug, autenticacao)
- CRUD de noticias (criacao, validacoes, busca, filtro por categoria)
- Busca publica multi-token (titulo, conteudo, nome/slug de categoria)
- Detalhe de noticia por slug (filtra drafts)
- Formulario de contato (validacao, dispatch de job)
- CRUD de instrumentos de mercado (criacao, busca)
- Feed config (auth, validacao, feed desabilitado, symbols por feed_id)

## Estrutura do Projeto

```
app/
├── Cache/                    # NewsCache (cache com versionamento)
├── Events/                   # MarketTickReceived (broadcast)
├── Http/
│   ├── Controllers/Api/      # Controllers da API
│   │   ├── Admin/            # CRUD admin (auth:sanctum)
│   │   ├── AuthController
│   │   │   ├── FeedConfigController
│   │   ├── MarketIngestController
│   │   ├── PublicNewsController
│   │   ├── PublicCategoryController
│   │   └── PublicContactController
│   ├── Requests/             # Form Requests (validacoes)
│   └── Resources/            # API Resources (respostas JSON)
├── Jobs/                     # ProcessContactSubmission
├── Models/                   # User, News, Category, Contact, MarketInstrument, MarketSchedule, FeedConfig
│   └── Traits/               # Searchable (busca multi-token reutilizavel)
├── Observers/                # NewsObserver (invalidacao de cache)
├── Repositories/             # Interface + Eloquent para cada entidade
│   ├── News/
│   ├── Contacts/
│   ├── Categories/
│   ├── MarketInstruments/
│   └── FeedConfigs/
├── Services/                 # Logica de negocio para cada entidade
│   ├── News/
│   ├── Contacts/
│   ├── Categories/
│   ├── MarketInstruments/
│   └── FeedConfigs/
└── Providers/                # AppServiceProvider, HorizonServiceProvider

resources/js/
├── app.jsx                   # Entry-point React
├── AppRoutes.jsx             # Rotas (React Router)
├── components/               # Layout, AdminLayout, MarketTickerWidget
├── hooks/                    # useAuth (context), useEcho (WebSocket), usePagination
├── pages/                    # Home, NewsDetail, Contact, admin/*
├── services/                 # api.js (Axios)
└── utils/                    # Formatacao de data

tests/Feature/                # Testes de feature (PHPUnit)
docker/                       # Dockerfile (PHP) e nginx config
```

## Deploy em HTTPS

Se a pagina abre em **HTTPS** mas o console bloqueia CSS/JS com *Mixed Content*:

1. Defina `APP_URL` com `https://` (ex.: `APP_URL=https://ckao.in`)
2. Configure `TRUSTED_PROXIES` para o proxy que envia `X-Forwarded-Proto` (ex.: `TRUSTED_PROXIES=*`)
3. Opcional: `FORCE_HTTPS=true` para forcar HTTPS na geracao de URLs

Depois: `php artisan config:clear` e `npm run build`.

## URL Online

> https://ckao.in
