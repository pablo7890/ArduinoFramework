# Testing

## Local WordPress (cloud sandbox, no MySQL)

```bash
bash .claude/skills/parafia-frontend/scripts/setup-local-wp.sh "$SCRATCH/wp" path/to/plugin-folder
# → WordPress (pl_PL) on SQLite + The Events Calendar + twentytwentyone,
#   plugin symlinked and activated, server on http://127.0.0.1:8099
#   admin / admin
```

- WP-CLI: `php $WP/wp-cli.phar --allow-root …` inside `$WP/wordpress`
  (TEC prints PHP 8.4 deprecations on CLI – add `2>/dev/null`).
- Start the server in the background (`php -S 127.0.0.1:8099 router.php`
  from `$WP/wordpress`). Never kill it with `pkill -f php` / a `ps | grep`
  pattern that also matches your own shell – find the PID in `/proc`.
- TEC from wordpress.org: use the versioned zip
  (`the-events-calendar.6.18.0.zip`); the unversioned URL may return only a
  readme.
- Seed content with `wp eval-file` (posts with categories, events with
  `tribe_create_event()`, a venue, event categories, pages with shortcodes).
- Debug a mystery 404 / redirect: a temporary mu-plugin hooking
  `status_header` that writes `debug_backtrace()` to a file.
- Fake other plugins with mu-plugins that print the same markup (e.g.
  `[intencje_mszalne]` with `.ki-*` classes).

## Screenshots and checks

- `scripts/wpshot.js "name|width|/path[|login]" …` → `W-name.png` full page,
  prints `{ ov, H, notices }` (ov = horizontal overflow in px, must be 0) and
  JS errors. `CLIP=1400 Y=0` → cropped `C-name.png`.
- Widths: 1366 and 1440 desktop (1920 for the user's screen), 390 phone.
- Find the overflowing element: walk `document.querySelectorAll('body *')`
  for `getBoundingClientRect().right > innerWidth` (usually a grid without
  `minmax(0,1fr)`).
- `scripts/crawl.py` – BFS over internal links from start URLs, flags pages
  whose `<main>` has no `class="pio …"` view or still contains TEC/theme
  markup (`tribe-events-c-*`, `tribe-events-calendar-*`, notices).
- Hover states: Playwright `locator.hover()` then clip screenshot.
- Images can't be cropped with PIL (not installed) – use Playwright `clip`.

## Live test site (real Avada)

- `scripts/live-shot.js <width> <path> <out> [inject]` – serialises requests
  and retries (the egress proxy drops bursts), blocks analytics; with
  `inject` it serves the local built CSS/JS instead of the plugin's, so a fix
  can be verified against real Avada before shipping. Prints `--pio-top` and
  sticky positions; adapt the `evaluate` block to measure what you need
  (widths, computed colours of a selector).
- `curl -sL https://srv95356.seohost.com.pl/...` works for HTML; check body
  classes, which plugin folder/version is installed, Avada CSS variables
  (`--site_width`).

## Preview artifact

`php preview/build.php "$SCRATCH/out.html"` then publish with the Artifact
tool (same path → same URL). Check tabs with Playwright at 1440 and 390.
