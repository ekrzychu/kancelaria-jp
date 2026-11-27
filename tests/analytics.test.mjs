import assert from 'node:assert/strict';
import { randomBytes } from 'node:crypto';
import { readFile } from 'node:fs/promises';
import { request as httpRequest } from 'node:http';
import { resolve } from 'node:path';
import test from 'node:test';
import { livewire, startBrowser } from './support/chrome.mjs';
import { startSite, until } from './support/site.mjs';

const agent = 'Mozilla/5.0 Synthetic browser';
const row = (site, sql, params = []) => site.database.query([{ sql, params }])[0];
const date = (offset = 0) => {
  const warsaw = new Intl.DateTimeFormat('en-CA', { year: 'numeric', month: '2-digit', day: '2-digit', timeZone: 'Europe/Warsaw' }).format(new Date());
  return new Date(Date.parse(warsaw + 'T00:00:00Z') + offset * 86400000).toISOString().slice(0, 10);
};
const request = (site, path, options = {}) => new Promise((resolve, reject) => {
  const request = httpRequest(site.origin + path, {
    ...options, headers: { 'User-Agent': agent, ...options.headers },
  }, response => {
    let body = '';
    response.setEncoding('utf8');
    response.on('data', chunk => { body += chunk; });
    response.on('end', () => resolve({ status: response.statusCode, body }));
    response.on('error', reject);
  });
  request.on('error', reject);
  request.end();
});

async function login(site, browser, email, password, destination) {
  await browser.navigate(site.adminOrigin + '/login');
  await livewire(browser, site).wire('Login', 'authenticate', { email, password });
  await until(() => browser.evaluate(path => location.pathname === path, destination), destination);
}

test('counted views are canonical daily aggregates, with bounded sources and failure isolation', async t => {
  const site = await startSite({ cached: true, analyticsEnabled: true });
  t.after(site.stop);
  const article = site.data.articles[0].slug;
  const service = site.data.services[0].slug;
  const document = site.data.site.documents[0].slug;
  const get = (path, headers = {}) => request(site, path, { headers });
  const privateMarker = 'private-analytics-fixture';
  for (const [path, referer] of [
    ['/?q=' + privateMarker, 'https://www.google.pl/path?' + privateMarker],
    ['/', 'https://www.google.pl/other'],
    ['/' + article + '/', 'https://l.facebook.com/path'],
    ['/specjalizacje/' + service + '/', 'https://unknown.example.test/' + privateMarker],
    ['/dokumenty/' + document + '/', site.origin + '/'],
  ]) assert.equal((await get(path, { Referer: referer })).status, 200);

  for (const [path, status, headers] of [
    ['/kontakt', 301, {}], ['/aktualnosci/', 301, {}], ['/sitemap.xml', 200, {}],
    ['/media/00000000000000000000000000000000', 404, {}], ['/assets/admin/panel.css', 200, {}],
    ['/unknown-fixture/', 404, {}], ['/podglad/artykuly/1', 404, {}], ['/livewire/update', 404, {}],
    ['/', 200, { 'User-Agent': 'Googlebot' }], ['/', 200, { 'User-Agent': '' }],
    ['/', 200, { 'Sec-Purpose': 'prefetch;prerender' }], ['/', 200, { 'X-Moz': 'prefetch' }],
    ['/', 200, { 'Sec-Fetch-Dest': 'iframe' }], ['/', 200, { 'X-Livewire': 'true' }],
    ['/', 200, { 'X-Requested-With': 'XMLHttpRequest' }],
  ]) assert.equal((await get(path, headers)).status, status, path);
  assert.equal((await request(site, '/', { method: 'HEAD' })).status, 200);
  assert.equal((await request(site, '/kontakt', { method: 'POST' })).status, 419);
  assert.equal((await fetch(site.adminOrigin + '/login', { headers: { 'User-Agent': agent } })).status, 200);

  const counters = () => row(site, 'SELECT * FROM daily_page_views ORDER BY page');
  assert.deepEqual(counters(), [
    { day: date(), page: 'article:' + article, source: 'social', views: 1 },
    { day: date(), page: 'document:' + document, source: 'internal', views: 1 },
    { day: date(), page: 'home', source: 'search', views: 2 },
    { day: date(), page: 'service:' + service, source: 'external', views: 1 },
  ]);

  const start = String(Date.now() / 1000 + 0.6);
  await Promise.all(Array.from({ length: 5 }, () => site.concurrentPanelCheck(['analytics-increment', start])));
  assert.equal(row(site, "SELECT views FROM daily_page_views WHERE page='home' AND source='unknown'")[0].views, 100);

  row(site, "INSERT INTO daily_page_views VALUES ('2000-01-01', 'home', 'internal', 5)");
  site.artisan(['analytics:maintain']);
  site.artisan(['analytics:maintain']);
  assert.deepEqual(row(site, "SELECT day, views FROM daily_page_views WHERE page='' OR day='2000-01-01'"), [{ day: date(), views: 0 }]);
  row(site, 'DROP TABLE daily_page_views');
  const response = await get('/?q=' + privateMarker);
  assert.equal(response.status, 200);
  assert.match(response.body, /<main id="main"/);
  const log = await readFile(resolve(site.dir, 'storage/logs/laravel.log'), 'utf8').catch(() => '');
  assert.doesNotMatch(log, /daily_page_views|private-analytics-fixture/);
});

test('statistics enforces its own permission and reports counts, previous ranges and unknown days', async t => {
  const site = await startSite({ cached: true });
  t.after(site.stop);
  const browser = await startBrowser();
  t.after(browser.close);
  const password = 'Fixture!' + randomBytes(16).toString('hex');
  site.panelChecks(['provision'], JSON.stringify({ name: 'Anna Nowak', email: 'admin@example.test', password }));
  row(site, `INSERT INTO users (name,email,password,is_active,permissions)
    SELECT 'Zofia Kowalska', 'report@example.test', password, 1, '["analytics"]' FROM users WHERE email='admin@example.test'`);
  row(site, `INSERT INTO users (name,email,password,is_active,permissions)
    SELECT 'Jan Kowalski', 'denied@example.test', password, 1, '["articles"]' FROM users WHERE email='admin@example.test'`);
  assert.equal((await request(site, '/')).status, 200);
  assert.equal((await request(site, '/statystyki/')).status, 404);
  assert.equal(row(site, 'SELECT COUNT(*) AS total FROM daily_page_views')[0].total, 0);
  assert.equal((await fetch(site.adminOrigin + '/statystyki', { redirect: 'manual' })).status, 302);
  await login(site, browser, 'denied@example.test', password, '/artykuly');
  const { evaluate } = browser;
  assert.equal(await evaluate(async () => (await fetch('/statystyki')).status), 403);
  assert.equal(await evaluate(() => !!document.querySelector('.fi-sidebar a[href$="/statystyki"]')), false);
  await evaluate(() => document.querySelector('form[action$="/logout"]').requestSubmit());
  await until(() => evaluate(() => location.pathname === '/login'), 'logout');
  await login(site, browser, 'report@example.test', password, '/statystyki');
  assert.equal(await evaluate(async () => (await fetch('/wiadomosci')).status), 403);
  assert.equal(await evaluate(() => document.querySelector('.fi-sidebar-item-label').textContent.trim()), 'Statystyki');
  const metric = key => evaluate(key => document.querySelector('[data-metric="' + key + '"]').textContent.trim(), key);
  assert.equal(await metric('views'), '—');
  assert.match(await evaluate(() => document.body.innerText), /Zliczanie odsłon jest wyłączone/);
  assert.equal(await evaluate(() => document.querySelector('[data-coverage]').dataset.coverage), 'missing');
  const range = async (from, to, period = 'custom') => evaluate(async (id, from, to, period) => {
    const page = Livewire.find(id);
    await page.$set('period', period, false);
    await page.$set('customStart', from, false);
    await page.$set('customEnd', to, false);
    await page.$call('applyRange');
  }, await livewire(browser, site).ready('Statistics'), from, to, period);
  const article = site.data.articles[0];
  const service = site.data.services[0];
  const count = (day, page, source, views) => row(site, 'INSERT INTO daily_page_views VALUES (?,?,?,?)', [day, page, source, views]);
  for (const [day, page, source, views] of [
    [date(), 'article:' + article.slug, 'social', 14],
    [date(), 'service:' + service.slug, 'search', 8],
    [date(-1), 'home', 'unknown', 4],
    [date(-2), 'articles.index', 'search', 9],
    [date(-3), 'contact', 'external', 5],
    [date(-4), 'services.index', 'internal', 12],
    [date(-5), 'home', 'search', 7],
    [date(-6), 'home', 'internal', 3],
    [date(-7), '', '', 0],
    [date(-8), 'home', 'unknown', 2],
  ]) count(day, page, source, views);
  await range(date(-1), date());
  await until(async () => await metric('views') === '26', 'combined current period');
  assert.equal(await metric('average'), '13,0');
  assert.match(await evaluate(() => document.querySelector('.panel-statistics-totals').innerText), /\+12 względem poprzedniego okresu/);
  assert.match(await evaluate(() => document.querySelector('.panel-statistics-rankings').textContent), /Wyszukiwarki|Media społecznościowe/);
  assert((await evaluate(() => document.querySelector('.panel-statistics-rankings').textContent)).includes(article.title));
  assert((await evaluate(() => document.querySelector('.panel-statistics-rankings').textContent)).includes(service.title));

  await range(date(-7), date(-7));
  await until(async () => await metric('views') === '0', 'recorded zero traffic');
  await range(date(-8), date());
  await until(() => evaluate(() => !document.querySelector('[data-coverage]')), 'coverage after contiguous dates');
  await range(date(-10), date());
  await until(() => evaluate(() => document.querySelector('[data-coverage]')?.dataset.coverage === 'partial'), 'partial coverage');
  await range('2000-01-01', date());
  await until(() => evaluate(() => document.body.innerText.includes('najwyżej 366 dni')), 'bounded date range');

  // Warsaw advanced to summer time on 29 March 2026. That local day spans only 23 UTC hours.
  for (const time of ['2026-03-28 22:59:59', '2026-03-28 23:00:00', '2026-03-29 21:59:59', '2026-03-29 22:00:00']) {
    row(site, "INSERT INTO contact_messages (id, name, email, message, created_at) VALUES (UUID(), 'Private fixture sender', 'private@example.test', 'Private fixture body', ?)", [time]);
  }
  await range('2026-03-29', '2026-03-29');
  await until(async () => await metric('contacts') === '2', 'Warsaw contact date boundaries');
  assert.match(await evaluate(() => document.querySelector('.panel-statistics-totals').innerText), /\+1 względem poprzedniego okresu/);
  assert.doesNotMatch(await evaluate(() => document.documentElement.outerHTML), /Private fixture|private@example.test/);

  for (let offset = -13; offset <= -9; offset++) count(date(offset), 'home', 'search', 6 - (offset % 5));
  await range('', '', '90');
  await until(() => evaluate(() => document.querySelector('[data-coverage]')?.textContent.includes('90 dni')), '90-day preset');
  await range('', '', '7');
  await until(() => evaluate(() => document.querySelector('[data-metric="views"]')?.textContent.trim() === '62'
    && document.querySelector('.fi-wi-chart .fi-async-component')?.dataset.loading !== 'true'
    && document.querySelector('canvas')?.getBoundingClientRect().height > 0), 'rendered 7-day chart');
  assert(!await evaluate(() => [...document.querySelectorAll('.fi-wi-chart *')].some(el =>
    [...el.attributes].some(attr => attr.name.startsWith('wire:poll')))));

  row(site, "UPDATE users SET permissions='[]' WHERE email='report@example.test'");
  assert.equal(await evaluate(async () => (await fetch('/statystyki')).status), 403);
  for (const name of ['statistics', 'daily-traffic']) {
    const status = await evaluate(async name => {
      const script = document.querySelector('script[data-update-uri]');
      const snapshot = [...document.querySelectorAll('[wire\\:snapshot]')].map(el => el.getAttribute('wire:snapshot'))
        .find(value => JSON.parse(value).memo.name.split(/[.\\]/).at(-1).toLowerCase() === name);
      if (!snapshot) throw new Error('Missing snapshot for ' + name);
      const response = await fetch(script.dataset.updateUri, {
        method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Livewire': '' },
        body: JSON.stringify({ _token: script.dataset.csrf, components: [{ snapshot, updates: {}, calls: [] }] }),
      });
      return response.status;
    }, name);
    assert.equal(status, 403, name);
  }
  const screenshotDir = process.env.ANALYTICS_SCREENSHOTS;
  if (screenshotDir) {
    await browser.call('Network.clearBrowserCookies');
    await browser.call('Emulation.setEmulatedMedia', { features: [{ name: 'prefers-reduced-motion', value: 'reduce' }] });
    await browser.viewport(1440, 1000);
    await login(site, browser, 'admin@example.test', password, '/artykuly');
    await browser.navigate(site.adminOrigin + '/statystyki');
    assert.equal(await evaluate(() => document.querySelector('.fi-sidebar-item-label').textContent.trim()), 'Statystyki');
    await range('', '', '7');
    await until(() => evaluate(() => document.querySelector('.fi-wi-chart .fi-async-component')?.dataset.loading !== 'true'
      && document.querySelector('canvas')?.getBoundingClientRect().height > 0), 'screenshot chart');
    for (const [width, height, mobile] of [[1440, 1000, false], [390, 844, true]]) {
      await browser.viewport(width, height, mobile);
      await evaluate(async () => {
        window.scrollTo(0, 0);
        await document.fonts.ready;
        await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)));
      });
      assert(await evaluate(() => document.documentElement.scrollWidth <= innerWidth));
      const device = mobile ? 'mobile' : 'desktop';
      await browser.screenshot(screenshotDir, device);
      await evaluate(() => {
        const top = document.querySelector('.panel-statistics-rankings').getBoundingClientRect().top;
        window.scrollBy(0, top - 72);
      });
      await browser.screenshot(screenshotDir, device + '-rankings');
    }
  }
});
