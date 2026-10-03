#!/usr/bin/env python3
"""Fetch real posts and events from parafiapio.pl for the offline preview.

Writes preview/data.json (text only, remote image URLs). Images are downloaded
by build.php into a local cache.

    python3 preview/fetch.py
"""
import html
import json
import os
import re
import urllib.request
from datetime import date, timedelta

SITE = "https://parafiapio.pl"
HERE = os.path.dirname(os.path.abspath(__file__))


def get(url, with_headers=False):
    req = urllib.request.Request(url, headers={"User-Agent": "piodesign-preview/1.0"})
    with urllib.request.urlopen(req, timeout=60) as r:
        data = json.loads(r.read().decode("utf-8"))
        return (data, r.headers) if with_headers else data


def text(s):
    return html.unescape(re.sub(r"<[^>]+>", " ", s or "")).strip()


def post_images(content):
    """FooGallery thumbnails first, then inline images."""
    thumbs = re.findall(r'<img[^>]+src="([^"]+/uploads/cache/[^"]+)"[^>]*class="[^"]*fg-image', content)
    inline = re.findall(r'<img[^>]+class="[^"]*wp-image-\d+[^"]*"[^>]+src="([^"]+)"', content)
    inline += re.findall(r'<img[^>]+src="([^"]+)"[^>]+class="[^"]*wp-image-\d+', content)
    fg_count = len(re.findall(r'class="fg-item ', content))
    return thumbs + inline, max(fg_count, len(set(thumbs + inline)))


def main():
    posts, headers = get(f"{SITE}/wp-json/wp/v2/posts?per_page=24&_embed=wp:featuredmedia,wp:term", True)
    total_posts = int(headers.get("X-WP-Total", len(posts)))
    out_posts = []
    for p in posts:
        fm = (p.get("_embedded", {}).get("wp:featuredmedia") or [{}])[0]
        sizes = fm.get("media_details", {}).get("sizes", {})
        large = sizes.get("large") or sizes.get("full") or {}
        full = fm.get("media_details", {})
        cats = [t for g in p.get("_embedded", {}).get("wp:term", []) for t in g if t.get("taxonomy") == "category"]
        imgs, count = post_images(p["content"]["rendered"])
        out_posts.append({
            "id": p["id"],
            "title": html.unescape(p["title"]["rendered"]),
            "url": p["link"],
            "date": p["date"],
            "slug": p["slug"],
            "excerpt": text(p["excerpt"]["rendered"]),
            "category": {"name": html.unescape(cats[0]["name"]), "slug": cats[0]["slug"]} if cats else None,
            "image": {
                "src": large.get("source_url") or fm.get("source_url"),
                "w": full.get("width") or large.get("width"),
                "h": full.get("height") or large.get("height"),
                "alt": fm.get("alt_text", ""),
            } if fm.get("source_url") else None,
            "gallery": [u for u in imgs if u != (large.get("source_url"))][:3],
            "photo_count": count,
            "words": len(text(p["content"]["rendered"]).split()),
        })

    today = date.today()
    monday = today - timedelta(days=today.weekday())
    events = get(f"{SITE}/wp-json/tribe/events/v1/events?per_page=50&start_date={monday - timedelta(days=35)}&end_date={today + timedelta(days=120)}")["events"]
    out_events = []
    for e in events:
        venue = e.get("venue") or {}
        org = (e.get("organizer") or [{}])
        org = org[0] if org else {}
        img = e.get("image") or {}
        out_events.append({
            "id": e["id"],
            "title": html.unescape(e["title"]),
            "url": e["url"],
            "start": e["start_date"],
            "end": e["end_date"],
            "all_day": e["all_day"],
            "excerpt": text(e.get("excerpt") or e.get("description"))[:400],
            "content": e.get("description", ""),
            "image": {"src": img.get("url"), "w": img.get("width"), "h": img.get("height")} if img else None,
            "venue": {
                "name": html.unescape(venue.get("venue", "")),
                "address": venue.get("address", ""),
                "city": venue.get("city", ""),
            } if venue else {},
            "organizer": {"name": html.unescape(org.get("organizer", "")), "url": org.get("website") or org.get("url", "")} if org else {},
            "category": ({"name": html.unescape(e["categories"][0]["name"]), "slug": e["categories"][0]["slug"]} if e.get("categories") else None),
            "featured": e.get("featured", False),
            "cost": e.get("cost", ""),
        })

    parent = get(f"{SITE}/wp-json/wp/v2/pages?slug=sakramenty-i-sakramentalia&_fields=id")[0]["id"]
    sacraments = [
        {"slug": p["slug"], "title": html.unescape(p["title"]["rendered"]), "url": p["link"], "excerpt": text(p["excerpt"]["rendered"])}
        for p in get(f"{SITE}/wp-json/wp/v2/pages?parent={parent}&per_page=20&orderby=menu_order&order=asc&_fields=slug,title,link,excerpt")
    ]

    # Mass times from the intentions page (this week and the next), for the "next Mass" counter.
    sunday = today - timedelta(days=(today.weekday() + 1) % 7)
    slots = []
    for week in (sunday, sunday + timedelta(days=7)):
        req = urllib.request.Request(f"{SITE}/intencje-mszalne/?tydzien={week}", headers={"User-Agent": "piodesign-preview/1.0"})
        page = urllib.request.urlopen(req, timeout=60).read().decode("utf-8")
        for block in re.split(r'class="[^"]*\bki-dzien-item\b[^"]*"', page)[1:]:
            d = re.search(r'class="[^"]*\bki-data\b[^"]*"[^>]*>\s*(\d{1,2})\.(\d{1,2})\.(\d{4})', block)
            if not d:
                continue
            for h, m in re.findall(r'class="[^"]*\bki-godzina\b[^"]*"[^>]*>\s*(\d{1,2})[:.](\d{2})', block):
                slots.append(f"{d.group(3)}-{int(d.group(2)):02d}-{int(d.group(1)):02d}T{int(h):02d}:{m}")
    slots = sorted(set(slots))

    # One full post for the single post view.
    full = get(f"{SITE}/wp-json/wp/v2/posts/{out_posts[1]['id']}")
    single_post = {"id": full["id"], "content": full["content"]["rendered"], "excerpt": text(full["excerpt"]["rendered"])}

    with open(os.path.join(HERE, "data.json"), "w", encoding="utf-8") as f:
        json.dump({"posts": out_posts, "total_posts": total_posts, "events": out_events, "sacraments": sacraments, "mass_slots": slots, "single_post": single_post}, f, ensure_ascii=False, indent=1)
    print(f"{len(out_posts)} posts, {len(out_events)} events")


if __name__ == "__main__":
    main()
