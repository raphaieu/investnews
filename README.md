# InvestNews

Sistema de gerenciamento e consulta de notícias para investidores, desenvolvido como desafio técnico para a Investidor10.

## Stack

- **Backend:** Laravel 13 (PHP 8.3) com Sanctum SPA
- **Frontend:** React 18 + Tailwind CSS (embutido via Vite)
- **Banco:** MySQL 8
- **Infra:** Docker Compose (nginx + php-fpm + mysql)

## Funcionalidades

- **Área pública:** listagem de notícias com busca por título ou categoria, filtro por categoria, paginação e detalhe por slug
- **Área admin:** login, CRUD completo de categorias e notícias com validações
- **API REST** com Form Requests, API Resources e middleware de autenticação

## Decisões técnicas

- **React via Vite dentro do Laravel** para manter deploy único e demonstrar competência full stack no ecossistema Laravel. Sem necessidade de repositório separado, CORS ou complexidade de dois deploys.
- **Sanctum modo SPA** (cookie-based) para autenticação — mais simples e seguro que tokens para comunicação same-origin.
- **Form Requests** para validações separadas dos controllers, mantendo controllers finos.
- **API Resources** para respostas JSON consistentes e desacopladas dos models.
- **Scopes no Model** (`published`, `search`, `inCategory`) para queries reutilizáveis e controllers limpos.
- **Factories e Seeders** com dados realistas em pt-BR para facilitar avaliação.

## Setup local com Docker

```bash
# 1. Clonar e configurar
git clone git@github.com:raphaieu/investnews.git
cd investnews
cp .env.example .env

# 2. Subir containers
docker compose up -d

# 3. Instalar dependências, gerar key, rodar migrations e seeds
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed

# 4. Build do frontend
docker compose run --rm node

# 5. Acessar
# http://localhost:8000
```

## Setup local sem Docker

```bash
# Requer PHP 8.3+, Composer, Node 22+, MySQL 8

composer install
cp .env.example .env
# Ajustar DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD no .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

## Credenciais do admin (seedado)

- **E-mail:** [admin@investnews.com](mailto:admin@investnews.com)
- **Senha:** password

## Executar testes

```bash
php artisan test
```

Os testes cobrem:

- Login de admin (sucesso e falha)
- CRUD de categorias (criação, validações, unicidade, autenticação)
- CRUD de notícias (criação, validações)
- Busca pública por título e categoria
- Filtro público por categoria
- Detalhe de notícia por slug

## Estrutura do projeto

```
app/
├── Http/
│   ├── Controllers/Api/         # Controllers da API
│   │   ├── Admin/               # CRUD admin (autenticado)
│   │   ├── AuthController.php   # Login/logout
│   │   ├── PublicNewsController.php
│   │   └── PublicCategoryController.php
│   ├── Requests/                # Form Requests (validações)
│   └── Resources/               # API Resources (respostas)
├── Models/                      # Eloquent models
resources/js/
├── app.jsx                      # Entry-point do React (Vite)
├── AppRoutes.jsx                # Configuração de rotas (React Router)
├── components/                  # Layout, AdminLayout
├── hooks/                       # useAuth (context)
├── pages/                       # Home, NewsDetail, admin/*
├── services/                    # api.js (axios)
└── utils/                       # Utilitários (ex.: formatação de data)
tests/Feature/                   # Testes de feature
docker/                          # Dockerfiles e configs
```

## Deploy em HTTPS (evitar mixed content)

Se a página abre em **HTTPS** mas o console bloqueia CSS/JS do Vite com *Mixed Content* (URLs em **http://**), configure:

1. **`APP_URL`** com `https://` (ex.: `APP_URL=https://ckao.in`).
2. **`TRUSTED_PROXIES`** atrás de nginx/Cloudflare/proxy que envia `X-Forwarded-Proto` (ex.: `TRUSTED_PROXIES=*` ou IPs do proxy).
3. Opcional: **`FORCE_HTTPS=true`** para forçar esquema HTTPS na geração de URLs em ambientes que não usam `APP_ENV=production`.

Depois: `php artisan config:clear` e garantir `npm run build` com os assets em `public/build`.

## URL online

> https://ckao.in

