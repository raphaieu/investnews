---
name: Feed Config
description: Multi-feed support for market data - feed_configs table controls enable/disable and interval per feed (mt5-forex, mt5-b3)
type: project
---

Added multi-feed support on 2026-04-02:
- `feed_configs` table: feed_id (unique), enabled, interval_sec
- `market_instruments.feed_id` column: links each symbol to a feed
- `GET /api/feed/config?feed_id=X` endpoint: MQL5 script queries before sending ticks
- Two feeds seeded: `mt5-forex` (international) and `mt5-b3` (Brazilian B3)
- Config file `market_instruments.php` changed from `symbol => name` to `symbol => ['display_name' => ..., 'feed_id' => ...]`

**Why:** Prepare for B3 market data feed alongside existing forex/CFD feed, with independent on/off control.

**How to apply:** Each feed can be toggled independently. The MQL5 script should call `/api/feed/config` on startup to get enabled status, interval, and symbol list.
