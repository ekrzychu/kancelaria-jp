import assert from 'node:assert/strict';
import { readFile, writeFile } from 'node:fs/promises';
import { join } from 'node:path';
import { test } from 'node:test';
import { routes, startSite } from './support/site.mjs';

test('public routing and disabled enquiry intake', async t => {
  const app = await startSite();
  t.after(app.stop);
  const { origin, dir, data } = app;
  const request = (path, options) => fetch(origin + path, { redirect: 'manual', ...options });
  const paths = routes(data);
  const canonical = data.site.url.replace(/\/$/, '');

  for (const path of paths) {
    await t.test('renders ' + path, async () => {
      const result = await request(path);
      const html = await result.text();
      assert.equal(result.status, 200, html);
      assert(html.includes('<link rel="canonical" href="' + canonical + path + '">'));
      assert.equal([...html.matchAll(/<h1(?:\s|>)/gi)].length, 1);
      const ids = [...html.matchAll(/<[^>]+\sid="([^"]+)"/g)].map(m => m[1]);
      assert.equal(ids.length, new Set(ids).size, 'Duplicate HTML id on ' + path);
    });
  }

  await t.test('the career timeline leaves the page metadata intact', async () => {
    const html = await (await request('/kariera/')).text();
    assert(html.includes('<title>Kariera — ' + data.site.name + '</title>'));
    assert(html.includes('<meta property="og:title" content="Kariera — ' + data.site.name + '">'));
    assert.doesNotMatch(html, /<meta name="description" content="Informację o wyniku rekrutacji/);
  });

  await t.test('unknown routes are served as noindex 404s', async () => {
    for (const path of ['/missing-regression-route/', '/specjalizacje/missing-regression-route/', '/dokumenty/missing-regression-route/', '/admin', '/admin/', '/admin/login', '/up', '/api/', '/publikacje/']) {
      const result = await request(path);
      assert.equal(result.status, 404);
      assert.match(await result.text(), /<meta name="robots" content="noindex,follow">/);
    }
  });

  await t.test('existing redirects and query string are preserved', async () => {
    for (const [from, to] of [
      ['/aktualnosci/', '/artykuly/'],
      ['/aktualnosci/?z=2&a=1&a=3', '/artykuly/?z=2&a=1&a=3'],
      ['/artykuly?rok=2026&sort=oldest', '/artykuly/?rok=2026&sort=oldest'],
      ...paths.filter(path => path !== '/').map(path => [path.slice(0, -1), path]),
    ]) {
      const result = await request(from);
      assert.equal(result.status, 301, from);
      assert.equal(result.headers.get('location'), to);
      await result.text();
    }
  });

  await t.test('repeated slashes and HEAD preserve the canonical page', async () => {
    const path = '/specjalizacje/' + data.services[0].slug + '/';
    const result = await request(path.replace('/specjalizacje/', '/specjalizacje//'));
    assert.equal(result.status, 200);
    assert((await result.text()).includes('<link rel="canonical" href="' + canonical + path + '">'));
    const head = await request(path.slice(0, -1), { method: 'HEAD' });
    assert.equal(head.status, 200);
    assert.equal(await head.text(), '');
  });

  await t.test('static crawler files and public response headers are preserved', async () => {
    const robots = await request('/robots.txt');
    assert.equal(robots.status, 200);
    assert((await robots.text()).includes('Sitemap: ' + canonical + '/sitemap.xml'));
    for (const path of ['/', '/artykuly', '/missing-regression-route/']) {
      const result = await request(path);
      assert.equal(result.headers.get('x-content-type-options'), 'nosniff');
      assert.equal(result.headers.get('x-frame-options'), 'SAMEORIGIN');
      assert.equal(result.headers.get('referrer-policy'), 'strict-origin-when-cross-origin');
      assert.equal(result.headers.get('permissions-policy'), 'camera=(), microphone=(), geolocation=()');
      await result.text();
    }
  });

  await t.test('sitemap matches the current indexable routes', async () => {
    const response = await request('/sitemap.xml');
    assert.equal(response.status, 200);
    const urls = [...(await response.text()).matchAll(/<loc>([^<]+)<\/loc>/g)].map(m => m[1]);
    assert.deepEqual(urls.sort(), paths.filter(path => !path.startsWith('/dokumenty/')).map(path => canonical + path).sort());
  });

  await t.test('contact keeps its layout, phone and email while inputs are disabled', async () => {
    for (const path of ['/kontakt/', '/kontakt/?wyslano=1']) {
      const response = await request(path);
      assert.equal(response.status, 200);
      assert.equal(response.headers.get('set-cookie'), null);
      const html = await response.text();
      assert.match(html, /<form class="contact-form" id="formularz"/);
      assert.match(html, /Formularz jest tymczasowo niedostępny/);
      assert(html.includes('href="tel:' + data.site.phone_href + '"'));
      assert(html.includes('href="mailto:' + data.site.email + '"'));
      for (const field of ['name', 'email', 'phone', 'message']) {
        assert.match(html, new RegExp('<(?:input|textarea)[^>]*name="' + field + '"[^>]*disabled'));
      }
      assert.match(html, /<button class="button contact-submit" type="submit" disabled>/);
      assert.doesNotMatch(html, /csrf_token|Wiadomość została zapisana|Wiadomość została wysłana/);
    }
  });

  // The JSONL persistence and throttling tests are preserved at the approved baseline commit.
  await t.test('direct POSTs cannot start intake or issue a success redirect', async () => {
    const sample = { name: 'Synthetic Example', email: 'fixture@example.test', message: 'Synthetic only.' };
    for (const path of ['/kontakt/', '/kontakt', '/kontakt/?wyslano=1']) {
      for (const [headers, body] of [
        [{}, new URLSearchParams(sample)],
        [{ 'content-type': 'application/json' }, JSON.stringify({ ...sample, csrf_token: 'fixture', website: 'fixture' })],
      ]) {
        const response = await request(path, { method: 'POST', headers, body });
        assert.equal(response.status, 405);
        assert.equal(response.headers.get('location'), null);
        assert.equal(response.headers.get('set-cookie'), null);
        assert.equal(response.headers.get('allow'), 'GET, HEAD');
        assert.doesNotMatch(await response.text(), /Wiadomość została zapisana|Wiadomość została wysłana/);
      }
    }
    for (const file of ['contact-submissions.jsonl', 'contact-rate-limit.json']) {
      await assert.rejects(readFile(join(dir, 'storage', file)), { code: 'ENOENT' });
    }
  });
});

test('homepage shows the three newest eligible publications with formatted dates', async t => {
  const { origin, dir, stop } = await startSite();
  t.after(stop);
  const fixtures = [
    { slug: 'oldest', date: '2023-12-31' },
    { slug: 'invalid-calendar-date', date: '2026-02-29' },
    { slug: 'previous', date: '2024-01-01' },
    { slug: 'missing-date' },
    { slug: 'noncanonical-date', date: '2026-1-01' },
    { slug: 'nonstring-date', date: 20260101 },
    { slug: 'newest', date: '2025-06-02' },
    { slug: 'leap-day', date: '2024-02-29' },
    { slug: 'blank-summary', date: '2026-01-01', summary: ' ' },
  ].map(entry => ({ title: entry.slug, summary: 'Synthetic preview.', ...entry }));
  await writeFile(join(dir, 'app', 'data', 'articles.json'), JSON.stringify(fixtures));

  const response = await fetch(origin + '/');
  assert.equal(response.status, 200);
  const html = await response.text();
  const previews = html.match(/<ol class="home-publications-list"[^>]*>([\s\S]*?)<\/ol>/)?.[1];
  assert(previews, 'Homepage publication list is missing');
  assert.deepEqual([...previews.matchAll(/href="\/([^"]+)\/"/g)].map(match => match[1]), ['newest', 'leap-day', 'previous']);
  assert.deepEqual([...previews.matchAll(/<time datetime="([^"]+)">([^<]+)<\/time>/g)].map(match => match.slice(1)), [
    ['2025-06-02', '02.06.2025'],
    ['2024-02-29', '29.02.2024'],
    ['2024-01-01', '01.01.2024'],
  ]);
});

test('related publications use the two newest valid entries out of 100', async t => {
  const { origin, dir, stop } = await startSite();
  t.after(stop);
  const rows = Array.from({ length: 100 }, (_, index) => ({
    slug: 'fixture-' + index,
    title: 'Fixture article ' + index,
    category: index >= 97 && index <= 98 ? 'Other category' : 'Current category',
    date: new Date(Date.UTC(2024, 0, index + 1)).toISOString().slice(0, 10),
    body_html: '<p>Synthetic article.</p>',
  }));
  const file = join(dir, 'app', 'data', 'articles.json');
  const current = rows[99];
  rows[96].slug = 'invalid/fixture';
  rows[96].date = '2099-01-01';
  for (const [dataset, expected] of [
    [[...rows.filter((_, i) => i % 2 === 0), ...rows.filter((_, i) => i % 2 !== 0).reverse()], ['fixture-98', 'fixture-97']],
    [[current, rows[0]], ['fixture-0']],
    [[current], []],
  ]) {
    await writeFile(file, JSON.stringify(dataset));
    const response = await fetch(origin + '/' + current.slug + '/');
    assert.equal(response.status, 200);
    const html = await response.text();
    const links = [...html.matchAll(/href="\/([^"]+)\/" aria-labelledby="article-related-\d+"/g)].map(match => match[1]);
    assert.deepEqual(links, expected);
    assert.match(html, /href="\/artykuly\/"/);
    assert.equal(html.includes('class="article-navigation"'), expected.length === 0);
  }
});

test('drafts and reserved article slugs are unavailable in public output', async t => {
  const { origin, dir, data, stop } = await startSite();
  t.after(stop);
  const marker = 'Unpublished fixture text 8914';
  const fixture = { title: marker, summary: marker, date: '2099-01-01', body_html: '<p>' + marker + '</p>' };
  const restricted = [
    { slug: 'fixture-draft', status: 'draft' },
    { slug: 'admin', status: 'published' },
    { slug: 'livewire', status: 'published' },
    { slug: 'up', status: 'published' },
  ];
  const { slug } = data.articles[0];
  await writeFile(join(dir, 'app', 'data', 'articles.json'), JSON.stringify([
    ...data.articles, ...restricted.map(entry => ({ ...fixture, ...entry })),
  ]));

  for (const entry of restricted) {
    const response = await fetch(origin + '/' + entry.slug + '/');
    assert.equal(response.status, 404, entry.slug);
    assert(!(await response.text()).includes(marker));
  }
  for (const route of ['/', '/artykuly/', '/' + slug + '/']) {
    const response = await fetch(origin + route);
    assert.equal(response.status, 200);
    assert(!(await response.text()).includes(marker));
  }
});
