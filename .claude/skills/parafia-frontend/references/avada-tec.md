# Avada, The Events Calendar and theme traps

Every item below broke something on parafiapio.pl once. Check new code
against all of them.

## Avada

| Symptom | Cause | Fix |
|---|---|---|
| Headings in the wrong font/size/colour, extra margin above titles | Avada styles bare `h1–h6`, `.post-content h2`, `.fusion-body h4` and loads **after** plugin CSS | Specificity build (`scripts/build-css.php`: first class doubled) + element resets inside `.pio :where(...)`. |
| White box behind quotes | Avada `blockquote` styles | Reset `blockquote, q, address, figure` in `:where()`. |
| `<br>` between two shortcodes | Avada Text Block autop | `.pio + br { display: none; }`; prefer Code Block. |
| Fonts not bold / not condensed although set in Global Options | Avada serves a static Google font without the `wdth` axis and with few weights | Bundle the variable font under a private family name (`PioDesign Display`), preload the woff2; keep an option to use Avada's face. |
| Links invisible (dark on dark) after the user visited them | `.fusion-body .tribe-common a:visited { color: var(--link_color) }` and `:hover`/`:focus` variants (0,3,1) | Don't render inside `.tribe-common`; give pills/buttons `color … !important` incl. `:visited`. |
| Sticky box hidden under the menu | Avada sticky header (~130 px, shrinks), admin bar 32 px | `top: calc(var(--pio-top,0px) + 24px)`; JS probes `elementsFromPoint` from y=1 down through stacked fixed/sticky bars and sets `--pio-top`; un-stick boxes taller than the viewport. |
| Event page shows Avada's "Szczegóły" sidebar | Avada option *Events → Event Meta Layout* | `add_filter( 'avada_setting_get_ec_meta_layout', … 'disabled' )` + hide `#sidebar` on that body class. |
| User's Avada Layout ignored / fought | Plugin `template_include` replaces the layout | Detect `Fusion_Template_Builder::get_instance()->get_override('content')`; skip our template; provide `[pio_wpis]` / `[pio_wydarzenie]`-style shortcodes for the layout. |
| Content wider than other pages inside a 100 % container | No max width on our view | `.pio-w-site { max-width: var(--site_width, 1200px); margin-inline: auto; }` behind an option. |
| CSS/JS stale after update | Same version string | Asset version = plugin version + `filemtime()`. |
| Avada typography variables | – | `--h1_typography-font-family`, `--body_typography-font-family`, `--awb-typography1-font-family`, `--site_width`, `--link_color`, `--primary_color`. |

## The Events Calendar (free, v2)

| Need | How |
|---|---|
| Replace one v2 partial | `tribe_template_file` filter, match the path suffix (`/v2/list/event.php`). `tribe_template_path_list` did **not** work. |
| Replace a whole view (list, month, day) | `tribe_events_views_v2_bootstrap_pre_get_view_html( $html, $slug, $query, $context )` → return our HTML. **Skip singular queries** (single events go through the same filter!). |
| Read the request | `$context->get( 'event_display' | 'event_display_mode' ('past') | 'event_date' ('Y-m' or 'Y-m-d') | 'event_category' (slug) | 'keyword' | 'paged' )`. `default` slug = `tribe_get_option( 'viewOption' )`. |
| Build URLs | `Tribe__Events__Main::instance()->getLink( 'list'|'month'|'day', $date, $term_id )`; list + date → `?tribe-bar-date=Y-m-d`; past → `?eventDisplay=past`; search → `?tribe-bar-search=`; page → `…/page/N/`. |
| Query events | `tribe_events()->where( 'status','publish' )->where( 'ends_after' | 'starts_before' | 'starts_after' | 'ends_before' | 'category', … )->search( $kw )->order_by( 'event_date', 'ASC' )->per_page()->page()->all()`, `->found()`. |
| Single event template | `tribe_events_template` filter for `single-event`; don't print `tribe_the_notices()` if the design shows the status itself. |
| 404 for months/days outside existing events (TEC 6.16+ SEO controller, `send_headers`) | Hook `send_headers` priority 20: if it's a TEC list/month/day request, reset `is_404`, `status_header(200)`, add `noindex` via `wp_robots`. |
| Disabled views 404 | Same hook; also merge `list, month, day` into `tribe_get_option_tribeEnableViews`. |
| Venue / organizer pages | Not public in TEC free – don't link to them; link the map (Google Maps search URL) instead. |
| TEC's own bar, subscribe dropdown, ical button | Not rendered when the view is taken over; provide our own search and an optional "Subskrybuj" (`webcal://…/?ical=1`, Google `render?cid=`). |
| Week start | `get_option( 'start_of_week' )` – parafiapio.pl uses Sunday-first in TEC; follow the option. |
| Multi-day event ending at 00:00 | Belongs to the previous day. |
| All-day end | `_EventEndDate` is 23:59:59. |

## WordPress / general

- Body classes: drop `has-sidebar` / `double-sidebars` on our views.
- Archives taken over: home (posts page), category, tag, date (incl. day),
  author, post search (`post_type=post`), a chosen "Aktualności" page – but
  never when `tribe_is_event_query()`.
- Static page used as archive: page number from `get_query_var( 'page' )`.
- Shortcodes rendering the current post: guard against recursion with a
  static flag.
- Fragment cache: `piodesign_cached()` – transient keyed by name + atts +
  version option + date; bypass for logged-in users; bump on `save_post`
  and on settings update.
- Oembed (Spotify): cache the episode title daily in a transient.
- Reading another plugin's HTML (e.g. intentions): render its shortcode with
  a recursion guard, parse with DOMDocument/regex, cache hourly.
