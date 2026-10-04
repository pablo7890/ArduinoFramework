# Workflow

## A round of work

1. **Parse the request.** The user sends numbered points, often with
   screenshots. Create one task per point; questions get answers, problems get
   a root cause before a fix ("why is it invisible?" → measure the computed
   style on the real site).
2. **Look at the real site.** `scripts/live-shot.js` screenshots the test site
   and can inject local CSS/JS (`INJECT=1`) to prove a fix against real Avada
   before shipping. `measure` mode prints widths / colours of selectors.
3. **Design before code** for anything new: decide the layout at three widths
   (1920/1366, tablet, 390) and the empty / overflowing states (no items, 1
   item, 30 items, very long titles, multi-day items, past items).
4. **Implement** in the plugin with its own prefix:
   - PHP: pure helpers (no WP calls) in `core.php`-like files so the offline
     preview can render them; WP queries in separate functions;
     templates in `templates/` rendered with `<prefix>_render()`.
   - CSS in `assets/src/`, built with `scripts/build-css.php`.
   - JS: one dependency-free IIFE, `init*` functions called from `boot()`.
   - Settings: schema-driven, tabbed (`piodesign/includes/settings.php`).
5. **Verify** (see `testing.md`): lint, local WP screenshots desktop+mobile,
   overflow 0, PHP notices 0, crawl for leftovers, preview build, optional live
   check with injected assets.
6. **Deliver**:
   - Bump `Version:` header and the version constant.
   - README (Polish): what's new, settings, shortcodes with parameter tables,
     how to update; changelog entry.
   - `git add -A && git commit` (message in English, imperative; end with the
     attribution lines from the system reminder) and push to the session branch.
   - Zip: `zip -qr <slug>-<ver>.zip <slug> -x '*/tools/*'` (zip files are
     gitignored), send with SendUserFile.
   - Republish the preview artifact from the same file path (keeps the URL).
   - Reply in Polish, point by point: what changed, what was verified and
     where (local WP / test site / preview), what the user must do (e.g.
     deactivate & delete an old folder before uploading a renamed plugin).

## Preview artifact

`preview/build.php` renders the plugin's real templates with WP shims
(`esc_*`, `apply_filters` stub with demo data, `piodesign_option` stub,
`Tribe__Events__Main` stub returning `#tab` links) on data fetched from the
live site (`preview/fetch.py` → `data.json`), inlines images and fonts, and
writes one HTML fragment (no `<head>`; the artifact host adds it – view it
locally by prefixing `<meta charset="utf-8">`). Tabs in `preview/shell.html`.

## Answer style

Short sections per point, bold labels, concrete numbers ("1200 px", "130 px"),
no promises about what wasn't tested. If something the user reports can't be
reproduced, say what you measured and offer the most likely cause plus a
setting/shortcode that covers it.
