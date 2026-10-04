# Parish data sources

What other cruzLabs / parish plugins expose, as used by PioDesign. When a
future session redesigns one of these plugins, keep these interfaces stable
(other plugins depend on them) and add to this file.

## „Parafia: Kalendarz liturgiczny”

- `apply_filters( 'kalendarz_liturgiczny_day', null, 'Y-m-d' )` → array|null
- `apply_filters( 'kalendarz_liturgiczny_days', [], 'Y-m-d', 'Y-m-d' )` →
  `[ 'Y-m-d' => day, … ]` (range, cheaper than one call per day)
- `kalendarz_liturgiczny_day_html` filter, a shortcode and a REST route also
  exist.
- Day fields: `title`, `rank_label` (e.g. „dzień powszedni”, „wspomnienie
  obowiązkowe”, „niedziela”), `color_label` („zielony”), `color_hex`,
  `readings` (sigla string: `"Hi 42,1-3.5-6.12-17; Ps 119; Łk 10,17-24"`),
  `optional_memorials` (array|string), `parish` (parish feasts),
  `occasional`.
- If a year is not approved yet the filters return null → PioDesign computes
  the season and colour itself (`piodesign_liturgy()`).
- Readings are labelled by PioDesign (`piodesign_readings()`): I czytanie,
  Psalm (also a Gospel canticle before the Gospel), II czytanie, Ewangelia.

## Intencje mszalne (Mass intentions page `/intencje-mszalne/`)

- Rendered by a shortcode whose tag contains `intenc`. The week is chosen with
  `?tydzien=Y-m-d`; weeks start on **Sunday**.
- Markup PioDesign parses: each day `.ki-dzien-item`, its date in `.ki-data`
  (`dd.mm.yyyy`), each Mass time in `.ki-godzina` (`HH:MM`).
- PioDesign reads this week + next week, caches hourly, and falls back to the
  times in its settings. Filter `piodesign_mass_slots` can supply
  `['Y-m-dTH:i', …]` directly – the cleanest integration if the intentions
  plugin is redesigned (add a PHP API and keep the markup classes).

## The Events Calendar

- Post type `tribe_events`, taxonomy `tribe_events_cat`, meta
  `_EventStartDate`, `_EventEndDate` (local time), `_tribe_featured`,
  all-day via `tribe_event_is_all_day()`, venue via `tribe_get_venue_id()`.
- PioDesign normalises events with `piodesign_wp_event()` → array with
  `start_ts`, `end_ts`, `ymd`, `end_ymd`, `when`, `time`, `status`
  (upcoming/ongoing/past), `relative` („jutro”), `multi`, `all_day`,
  `image`, `venue`, `category`, `gcal`, `ics`.

## PioDesign itself

- Shortcodes: `[pio_aktualnosci]`, `[pio_wydarzenia]`, `[pio_archiwum]`,
  `[pio_sakramenty]`, `[pio_cytaty]`, `[pio_liturgia]`, `[pio_informacje]`,
  `[pio_wpis]`, `[pio_wydarzenie]`.
- Options in `piodesign_settings` (`piodesign_option( key )`).
- Filters: `piodesign_tec_views`, `piodesign_tec_list`, `piodesign_tec_single`,
  `piodesign_news_archive`, `piodesign_single_post`, `piodesign_category_color`,
  `piodesign_event_category_color`, `piodesign_mass_slots`,
  `piodesign_cache_ttl`, `piodesign_template`.
- A new plugin that wants the same look can check
  `defined( 'PIODESIGN_VERSION' )` and reuse its CSS tokens, but must still
  work (with `assets/starter.css`) when PioDesign is not active.
