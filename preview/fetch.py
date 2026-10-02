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


def get(url):
    req = urllib.request.Request(url, headers={"User-Agent": "pio-gazeta-preview/1.0"})
    with urllib.request.urlopen(req, timeout=60) as r:
        return json.loads(r.read().decode("utf-8"))


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
    posts = get(f"{SITE}/wp-json/wp/v2/posts?per_page=15&_embed=wp:featuredmedia,wp:term")
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

    with open(os.path.join(HERE, "data.json"), "w", encoding="utf-8") as f:
        json.dump({"posts": out_posts, "events": out_events}, f, ensure_ascii=False, indent=1)
    print(f"{len(out_posts)} posts, {len(out_events)} events")


if __name__ == "__main__":
    main()
