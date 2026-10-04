#!/usr/bin/env python3
"""Crawl a local site and flag pages that don't use the plugin's own views.

    python3 crawl.py --base http://127.0.0.1:8099 --marker 'class="pio pio-' / /events/lista/ /aktualnosci/

A page is OK when <main> contains the marker and none of the "leftover"
patterns (TEC default markup, theme notices). Prints one line per URL, sorted;
problems end with <<<.
"""
import argparse, collections, re, urllib.error, urllib.parse, urllib.request

ap = argparse.ArgumentParser()
ap.add_argument('--base', default='http://127.0.0.1:8099')
ap.add_argument('--marker', default='class="pio pio-')
ap.add_argument('--leftover', default=r'class="(tribe-events-c-[a-z-]+|tribe-events-calendar-[a-z-]+|tribe-events-notices|tribe-events-l-container)')
ap.add_argument('--skip', default=r'wp-admin|wp-login|/feed|ical=|outlook-ical|wp-json|xmlrpc|\.(jpg|jpeg|png|webp|gif|css|js|xml|pdf)$|#|replytocom|/comments/|\?p=|wp-content|page/[3-9]')
ap.add_argument('--limit', type=int, default=200)
ap.add_argument('start', nargs='*', default=['/'])
a = ap.parse_args()

skip = re.compile(a.skip)
seen, queue, rows = set(), collections.deque(a.start), []

def norm(u):
    u = urllib.parse.urljoin(a.base + '/', u.replace('&amp;', '&').replace('&#038;', '&'))
    return (u[len(a.base):] or '/') if u.startswith(a.base) else None

while queue and len(seen) < a.limit:
    path = queue.popleft()
    if path in seen:
        continue
    seen.add(path)
    try:
        r = urllib.request.urlopen(a.base + path, timeout=60)
        code, html = r.status, r.read().decode('utf8', 'ignore')
    except urllib.error.HTTPError as e:
        code, html = e.code, e.read().decode('utf8', 'ignore')
    main = html.split('<main', 1)[-1]
    ours = a.marker in main
    left = sorted(set(re.findall(a.leftover, main)))[:4]
    rows.append((path, code, ours, left))
    for h in re.findall(r'href="([^"]+)"', html):
        n = norm(h)
        if n and not skip.search(n) and n not in seen:
            queue.append(n)

for path, code, ours, left in sorted(rows):
    bad = code >= 400 or not ours or left
    print(code, path, 'ours' if ours else '-', left or '', '  <<<' if bad else '')
