---
name: parafia-frontend
description: Design system, working method and hard-won fixes for the front end of cruzLabs WordPress plugins for parafiapio.pl (Parafia św. Ojca Pio, Gdańsk) – Avada theme, Avada Layouts, The Events Calendar, the parish's own plugins (Kalendarz liturgiczny, Intencje mszalne, PioDesign). Use whenever building or redesigning views, shortcodes, templates, CSS or settings pages of any of these plugins, when the user sends screenshots of layout problems on parafiapio.pl or the test site, or asks for a "nowy wygląd", "frontend", "widok", "shortcode" or "design" of a parish plugin.
---

# Parafia frontend (cruzLabs × parafiapio.pl)

You are continuing a design system that already ships in the plugin
**Parafia: PioDesign** (`piodesign/` in `pablo7890/arduinoframework`). Every new
front end for the user's other plugins must look like it belongs to the same
family and must avoid the mistakes listed here – each of them cost a round of
user feedback once.

Read these before writing code:

| File | When |
|---|---|
| `references/design-system.md` | Always: tokens, type, components, motion, copy. |
| `references/avada-tec.md` | Always for WordPress output: Avada / TEC / theme traps and their fixes. |
| `references/workflow.md` | Planning a round of work and delivering it. |
| `references/testing.md` | Setting up local WordPress, screenshots, crawling, live checks. |
| `references/parish-data.md` | Reading data from the parish plugins (liturgical calendar, Mass intentions, TEC). |
| `assets/starter.css` | Copy into a new plugin as the base of its stylesheet. |
| `scripts/` | `build-css.php`, `setup-local-wp.sh`, `wpshot.js`, `crawl.py`, `live-shot.js`. |

If the PioDesign plugin is in the repo, treat its code as the reference
implementation (`piodesign/assets/src/piodesign.css`, `piodesign/templates/`,
`piodesign/includes/`). Reuse its partials and patterns rather than inventing
new ones.

## The user and the site

- The user (GitHub pablo7890) publishes as **cruzLabs** and writes in Polish –
  answer in Polish, point by point, matching their numbering. Plugins are named
  „Parafia: <Nazwa>”, author „cruzLabs”, version starts at 1.0.
- Site: WordPress + **Avada** (Global Options typography: headings
  *Bricolage Grotesque*, body *Newsreader*; site width 1200 px; sticky header
  ~130 px that shrinks on scroll), **The Events Calendar** (free, v2 views,
  Polish slugs `/wydarzenia/`, `/wydarzenie/`, `lista`, `miesiac`,
  `dzisiaj`, `kategoria`), parish plugins listed in `parish-data.md`.
- Production: https://parafiapio.pl – test copy: https://srv95356.seohost.com.pl
  (reachable from the sandbox; use it to measure and screenshot real Avada
  output, see `testing.md`).
- The user builds pages in Avada Builder and Avada Layouts; shortcodes go into
  Code Block elements inside **Site Width** containers.

## What the user expects (from their feedback)

1. **Distinctive, editorial, "przekozacki"** – newsroom layouts, big condensed
   titles, small tracked kickers, a liturgical-colour accent, purposeful
   motion. Never a generic card grid that "every other site has".
2. **No wasted space.** Balance columns (stretch the shorter one, move
   secondary info under the title rather than into a tall side column), keep
   the gap under a section heading's rule small (≈18–24 px), never leave a
   white hole next to a short column. Re-check at 1366, 1440 and 1920 px.
3. **Site width, always.** Views sit at Avada's site width
   (`var(--site_width)`), never full-bleed unless asked. Single views get a
   width option and a shortcode for Avada Layouts.
4. **Nothing of the default look may leak.** Every link reachable from our
   views (pagination, categories, tags, day/month/past views, empty results,
   search, 404-ish dates, single items) must land on our styled view. Crawl it
   (`scripts/crawl.py`) before delivering.
5. **Mobile first-class:** show fewer items (5) plus a "see all" button, hide
   secondary panels (e.g. the liturgical-day "ear"), no horizontal overflow at
   360–390 px, tap targets ≥ 40 px.
6. **Configurable:** a tabbed settings page with as many options as is
   reasonable (titles, kickers, counts, excerpt lengths per card size,
   toggles, sources); shortcode attributes override settings; sensible
   defaults so it works with zero configuration.
7. **Tested, finished product:** each round ends with a zip ready to upload,
   bumped version, README + changelog in Polish, an updated artifact preview,
   and an honest list of what was verified where.

## Non-negotiable engineering rules (details in `avada-tec.md`)

- Own CSS prefix per plugin (`pio-` is PioDesign's); BEM-ish names.
- Write source CSS in `assets/src/`, build with `scripts/build-css.php`
  (doubles the first class of each selector so Avada's later, element-level
  rules lose). Element resets go through `:where()`.
- Colours that must survive Avada's `a:visited` / `.tribe-common a` rules get
  `!important` (links on TEC pages especially).
- `font:` shorthand with `!important` resets `font-stretch` – repeat
  `font-stretch: …% !important` after it.
- Grids that hold text: `grid-template-columns: minmax(0, 1fr)` and
  `min-width: 0` on flex/grid children, or long titles push the page wider
  than the phone.
- Sticky elements use `top: calc(var(--pio-top, 0px) + 24px)`; the JS in
  PioDesign (`initStickyTop`) measures stacked fixed headers + admin bar.
- Never `filter:` on an element whose content ticks (countdowns) – it
  repaints and flickers. Use static `box-shadow`.
- Take over whole views (TEC `tribe_events_views_v2_bootstrap_pre_get_view_html`,
  `template_include` at priority 99) instead of patching fragments; keep a
  filter to switch each takeover off.
- Respect Avada Layouts: if `Fusion_Template_Builder` overrides the content,
  don't replace the template – offer a shortcode for the layout.
- Cache rendered fragments (transient, versioned key bumped on save) but never
  for logged-in users; anything time-relative is recalculated in JS.

## Workflow in one screen (details in `workflow.md`)

1. Restate the numbered requests; create a task per item.
2. Look at the real thing first: fetch / screenshot the test site, measure
   with Playwright (`scripts/live-shot.js`) – widths, colours, which CSS wins.
3. Build in the plugin; render the real templates offline for the artifact
   preview (`preview/build.php` pattern) and in local WordPress
   (`scripts/setup-local-wp.sh`).
4. Verify: PHP lint, `node --check`, screenshots at 1366/1440 + 390 px,
   overflow = 0, no PHP notices, crawl for leftovers, live check with your CSS
   injected when useful.
5. Deliver: bump version, README/changelog (Polish), commit + push to the
   session branch, zip (`<slug>-<version>.zip`, folder = slug), republish the
   preview artifact, answer point by point (what changed, what was verified,
   what the user must do – e.g. remove an old plugin folder).

## Keep this skill alive

When a round teaches something new (a theme trap, a user preference, a better
pattern), add it to the right reference file and to the log below, and commit
it with the work. Keep entries short and concrete.

### Lessons log

- 1.0–1.1: Avada headings/margins override plugins → specificity build +
  `:where()` resets. TEC v2 rows: `tribe_template_file`, not
  `tribe_template_path_list`.
- 1.1: Avada loads Bricolage without the width axis → bundle the variable font
  under its own family name for titles (option to fall back to Avada's face).
- 1.2: User wants option-rich settings, Mass times from the intentions page,
  Spotify Gospel podcast in the liturgy card, single post view.
- 1.2.1: Liturgy card had a tall side column → empty white block. Fix pattern:
  secondary info as chips under the title, the side column holds one card that
  stretches (`justify-content: space-between`).
- 1.3: Active pill invisible after visiting (Avada `a:visited` inside
  `.tribe-common`). Month view built from scratch (bars for multi-day events,
  compact grid + agenda on phones). TEC 6.18 answers out-of-range months/days
  with 404 – lift it on `send_headers` and send `noindex`. Sticky ticket hid
  under Avada's sticky header – measure the header in JS.
