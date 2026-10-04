// Screenshot / measure a page of the live test site, optionally with local assets injected.
//   node live-shot.js <width> <path> <out-prefix> [inject]
//   SITE=https://srv95356.seohost.com.pl  CSS=path/to/built.css  JS=path/to/plugin.js  ASSET=piodesign
// The sandbox proxy drops bursts of parallel requests, so requests are serialised and retried.
const { chromium } = require(process.env.PW || '/opt/node22/lib/node_modules/playwright');
const fs = require('fs');
(async () => {
  const [w, path, out, inject] = process.argv.slice(2);
  const site = process.env.SITE || 'https://srv95356.seohost.com.pl';
  const asset = new RegExp((process.env.ASSET || 'piodesign') + '\\.(css|js)');
  const b = await chromium.launch();
  const ctx = await b.newContext({ ignoreHTTPSErrors: true });
  const p = await ctx.newPage();
  let chain = Promise.resolve();
  await p.route('**/*', route => {
    chain = chain.then(async () => {
      const u = route.request().url();
      const m = u.match(asset);
      if (inject && m && process.env[m[1].toUpperCase()]) {
        return route.fulfill({ status: 200, contentType: m[1] === 'css' ? 'text/css' : 'application/javascript', body: fs.readFileSync(process.env[m[1].toUpperCase()], 'utf8') });
      }
      if (/googletagmanager|google-analytics|facebook\.net/.test(u)) return route.abort();
      for (let k = 0; k < 4; k++) {
        try { const r = await route.fetch({ timeout: 30000 }); return route.fulfill({ response: r }); }
        catch (e) { await new Promise(r => setTimeout(r, 500 * (k + 1))); }
      }
      return route.abort();
    });
  });
  await p.setViewportSize({ width: +w, height: 900 });
  await p.goto(site + path, { waitUntil: 'load', timeout: 300000 });
  await p.evaluate(async () => { for (let y = 0; y < document.documentElement.scrollHeight; y += 600) { window.scrollTo(0, y); await new Promise(r => setTimeout(r, 100)); } window.scrollTo(0, 0); });
  await p.waitForTimeout(2000);
  // Adapt: what to measure.
  const info = await p.evaluate(() => ({
    pioTop: getComputedStyle(document.documentElement).getPropertyValue('--pio-top'),
    siteWidth: getComputedStyle(document.documentElement).getPropertyValue('--site_width'),
    views: [...document.querySelectorAll('[class^="pio pio-"]')].slice(0, 6).map(e => e.className.split(' ')[1] + ':' + Math.round(e.getBoundingClientRect().width)),
    ov: document.documentElement.scrollWidth - innerWidth,
  }));
  console.log(out, JSON.stringify(info));
  await p.screenshot({ path: `${out}.png`, fullPage: true });
  await b.close();
})();
