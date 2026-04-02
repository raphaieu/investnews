---
name: Refactoring V2
description: Major refactoring done on 2026-04-02 - Repository Pattern for all entities, Searchable trait, usePagination hook, category colors from DB, Docker health checks
type: project
---

Refactoring round completed on 2026-04-02 with these changes:

1. **Repository Pattern** standardized for ALL entities (added Category, MarketInstrument repos+services)
2. **Searchable trait** (`App\Models\Traits\Searchable`) — configurable via `$searchable` and `$searchableRelations` properties
3. **usePagination hook** — centralized pagination+debounced search for admin pages
4. **Category colors** — `color` field in DB, selectable in admin form, consumed by Home.jsx via API
5. **MarketTickerWidget** — removed hardcoded SYMBOL_NAMES_FALLBACK, now uses display_name from API
6. **Docker health checks** — mysql (mysqladmin ping), redis (redis-cli ping), nginx (curl /up)
7. **Index on news.published_at** — migration added

**Why:** User requested alignment with best practices and consistency across architecture.

**How to apply:** All entities now follow Controller → Service → Repository → Model pattern. Search is via Searchable trait on models. Frontend admin list pages use usePagination hook.
