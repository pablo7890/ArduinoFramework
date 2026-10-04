# Design system

The reference implementation is `piodesign/assets/src/piodesign.css`. This
file explains the decisions so they can be carried into other plugins.

## Character

Editorial parish newspaper: a confident grotesque set very large and
condensed, small tracked uppercase kickers, warm off-white paper, one rust
accent, deep night-blue panels for "featured" content, and the **liturgical
colour of the day** as a quiet accent (stole stripe, dots, tints). Motion is
calm and meaningful (reveal on scroll, live countdowns, gallery preview on
hover), never decorative bouncing.

## Tokens (see `assets/starter.css`)

| Token | Value | Use |
|---|---|---|
| `--pio-ink` | `#1c1d21` | text, solid buttons, active pills |
| `--pio-paper` | `#ffffff` | cards, page |
| `--pio-mist` | `#f4f3f1` | quiet fills, chips, empty states |
| `--pio-night` / `--pio-night-2` | `#14161b` / `#1d2027` | featured panels (ticket, podcast card, Gospel tile) |
| `--pio-rust` | `#c14e00` | accent: kickers, today, Sundays, links in text |
| `--pio-gold` | `#dba562` | accent on night panels, outlined years |
| `--pio-muted` | `#62656d` | secondary text |
| `--pio-line` | `#e4e3e0` | hairlines |
| `--lit` | day's liturgical colour | stole, dots |
| info section bg | `#f5f3ef` + gold radial `rgba(219,165,98,.14)` | pre-footer |

Contextual aliases `--c-fg`, `--c-muted`, `--c-line`, `--c-accent`,
`--c-chip` are redefined on dark panels instead of overriding each component.

Category colours: posts – fixed map per slug (filter
`piodesign_category_color`); events – stable palette by slug hash (filter
`piodesign_event_category_color`). Shown as a 3–6 px left border + 9 % tint
(`color-mix(in srgb, var(--cat) 9%, var(--pio-paper))`).

## Type

- Display (`--pio-display`): Avada's H1 face → Bricolage Grotesque, bundled as
  "PioDesign Display" (variable: `wght 400–800`, `wdth 75–100`) so titles can
  be 800 / 75 %. Body (`--pio-text`): Avada's body face (Newsreader).
  Everything must also look right with a serif (Iowan Old Style) where
  `font-stretch` does nothing – check both.
- Section title ("mast"): kicker 14 px / 700 / `.14em` uppercase rust with a
  short rule before it, then the word at `clamp(2.5rem, 4.8vw, 4.1rem)`,
  800, stretch 75 %, line-height .86–.92, tracking `-.022em`.
- Month headings: same family, `Październik` + year as **outlined gold**
  (`color: transparent; -webkit-text-stroke: 1.5px var(--pio-gold)`).
- Card titles 1.25–1.6 rem / 700; meta 11–13 px tracked uppercase.
- Numbers: `font-variant-numeric: tabular-nums` for dates, times, counters.
- Polish typography: „cudzysłowy”, en dash with spaces – , non-breaking
  space after one-letter words where it matters, `text-wrap: balance` on
  headings.

## Layout

- Container = Avada site width; components use **container queries**
  (`container: name / inline-size`) so the same shortcode works in 1/1, 2/3 or
  1/3 columns.
- Section heading: `.pio-mast__top` grid (title | "ear" with the liturgical
  day), 14 px padding + 1 px rule, then content 18–24 px below. The ear is
  hidden on phones.
- Rows (agenda): date tile | body | 16:9 thumbnail; on phones date | body with
  the image on top.
- Cards: 16:9 frames (`.pio-frame`, `object-fit` chosen by aspect ratio:
  `contain` with blurred backdrop for odd ratios), a photo-count badge.
- Photos: always prefer landscape (≥ 1.2:1) – choose among the featured image
  and the post's gallery (`piodesign_pick_image()`); the hero must not repeat
  the photo the text opens with (move it up and strip it from the text).
- Term views (category/tag): crumbs back to the archive, huge name, outlined
  count in the term colour, date span, description, chips with the active one
  first and filled in the term colour, a colour ribbon under the header.
- Balance: when a side column is optional, put the extras under the title
  (chips) and let the remaining side card stretch to the main column height.

## Components (PioDesign names)

| Component | Notes |
|---|---|
| `pio-mast`, `pio-mast__kicker`, `pio-mast__word`, `pio-mast__day` (ear) | Section heading with the day ear. |
| `pio-chips` / `pio-chip` | Category filters; horizontal scroll with mask fade on phones; active = ink fill. |
| `pio-newsroom` + `post-card` partial (lead / side / card / brief) | News layout: 1 lead, 2 side, grid, briefs. |
| `event-row` partial | Agenda row with status pill ("jutro", "za 3 dni", "trwa"), progress bar for running multi-day events. |
| `timeline` partial | 5-week strip: dots for events, bars for multi-day, today ring. |
| `pio-ticket` | Single event "ticket": night top half, perforation, countdown, facts, add-to-calendar buttons; sticky. |
| `pio-cal` | Month grid: bars for multi-day events across the week, items per day, "+N więcej" → day view, liturgical dot + feast name, hover card; phones: compact grid with dots + agenda. |
| `pio-cal-empty` | Empty state: icon disc + title + helpful next step (clear search, other category, next event). |
| `pio-pager` | Prev / numbers / next with words ("Wcześniejsze", "Kolejne"). |
| `pio-btn`, `pio-btn--solid` | Pill buttons; icons are inline SVG via `piodesign_icon()`. |
| Carousel (`[data-pio-carousel]`) | Autoplay with progress, pause on hover/focus, swipe, keyboard. |
| `pio-liturgy` | Day card with solid stole stripe, readings strip (Gospel tile in night), podcast card. |
| `pio-info` | Pre-footer tiles: next Mass live, office open/closed live, copy-to-clipboard, click-to-load map. |

## Motion

- Ease `cubic-bezier(.2,.7,.1,1)`; durations .25–.6 s; reveal on scroll with
  `animation-timeline: view()` inside `@supports`; everything off under
  `prefers-reduced-motion`.
- View Transitions for filter changes where supported.
- Hover cards only for `(hover: hover)`.

## Copy

Polish, warm and concrete: „Nadchodzące wydarzenia”, „Minione wydarzenia”,
„Pełny kalendarz parafii”, „Dodaj do kalendarza”, „+2 więcej”, „Trwa teraz”,
„Bieżący miesiąc”, „Wyczyść wyszukiwanie”. Liturgical terms: dzień
powszedni, wspomnienie (dowolne/obowiązkowe), święto, uroczystość, kolor
liturgiczny, I czytanie, Psalm, II czytanie, Ewangelia. Pluralise with
`piodesign_plural( n, 'wydarzenie', 'wydarzenia', 'wydarzeń' )`.
