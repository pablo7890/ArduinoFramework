// Screenshots of local WordPress pages + overflow / PHP notice / JS error check.
//   node wpshot.js "name|width|/path[|login]" ...
//   BASE=http://127.0.0.1:8099  CLIP=1400 Y=0 (cropped C-name.png instead of full W-name.png)
const { chromium } = require(process.env.PW || '/opt/node22/lib/node_modules/playwright');
(async () => {
  const b = await chromium.launch();
  const base = process.env.BASE || 'http://127.0.0.1:8099';
  for (const spec of process.argv.slice(2)) {
    const [name, w, path, login] = spec.split('|');
    const ctx = await b.newContext({ viewport: { width: +w, height: 900 } });
    const p = await ctx.newPage();
    const errs = [];
    p.on('pageerror', e => errs.push('JS ' + e));
    p.on('console', m => { if (m.type() === 'error') errs.push('C ' + m.text().slice(0, 140)); });
    if (login) {
      await p.goto(base + '/wp-login.php');
      await p.fill('#user_login', 'admin'); await p.fill('#user_pass', 'admin');
      await Promise.all([p.waitForNavigation(), p.click('#wp-submit')]);
    }
    await p.goto(base + path, { waitUntil: 'load', timeout: 120000 });
    // Scroll through once so lazy images and scroll-driven reveals settle.
    await p.evaluate(async () => { for (let y = 0; y < document.documentElement.scrollHeight; y += 600) { window.scrollTo(0, y); await new Promise(r => setTimeout(r, 40)); } window.scrollTo(0, 0); });
    await p.addStyleTag({ content: '*{animation:none!important;transition:none!important}' });
    await p.waitForTimeout(800);
    const info = await p.evaluate(() => {
      const W = innerWidth, wide = [];
      document.querySelectorAll('body *').forEach(e => { const r = e.getBoundingClientRect(); if (r.right > W + 1 && r.width && wide.length < 4) wide.push((typeof e.className === 'string' ? e.className : e.tagName).slice(0, 60)); });
      return { ov: document.documentElement.scrollWidth - W, H: document.documentElement.scrollHeight, notices: (document.body.innerText.match(/(Warning|Notice|Deprecated|Fatal error):[^\n]{0,120}/g) || []).slice(0, 3), wide };
    });
    console.log(name, path, JSON.stringify(info), errs.slice(0, 4));
    const clip = process.env.CLIP ? { x: 0, y: +(process.env.Y || 0), width: +w, height: +process.env.CLIP } : null;
    await p.screenshot(clip ? { path: `C-${name}.png`, fullPage: true, clip } : { path: `W-${name}.png`, fullPage: true });
    await ctx.close();
  }
  await b.close();
})();
