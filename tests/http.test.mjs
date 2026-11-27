import assert from 'node:assert/strict';
import { test } from 'node:test';
import { routes, startSite } from './support/site.mjs';

test('public routing and contact page', async t => {
  const app = await startSite();
  t.after(app.stop);
  const { origin, data } = app;
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

  await t.test('temporary public failures preserve their status and use generic recovery copy', async () => {
    const rename = (from, to) => app.database.query([{ sql: 'RENAME TABLE ' + from + ' TO ' + to }]);
    rename('articles', 'unavailable_articles');
    try {
      const result = await request('/artykuly/');
      assert.equal(result.status, 503);
      assert.match(result.headers.get('cache-control'), /no-store/);
      const html = await result.text();
      assert.match(html, /Usługa jest tymczasowo niedostępna/);
      assert.match(html, /href="mailto:kontakt@kancelaria-jp\.pl"/);
      assert.doesNotMatch(html, /SQLSTATE|unavailable_articles|QueryException/);
    } finally {
      rename('unavailable_articles', 'articles');
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

  await t.test('formal documents render their contents and preserve legacy links', async () => {
    for (const [slug, oldPath, headings, evidence] of [
      ['kodeks-etyczny', '/wp-content/uploads/Kodeks-Etyczny.pdf', 7, /1 stycznia 2024/],
      ['strategia-esg', '/wp-content/uploads/Strategia-zrownowazonego-rozwoju.pdf', 5, /Marta Jaroch.Konwent/],
      ['polityka-prywatnosci', '/polityka-prywatnosci/', 8, /Projekt do zatwierdzenia/],
    ]) {
      const path = '/dokumenty/' + slug + '/';
      const page = await (await request(path)).text();
      const toc = page.match(/<nav aria-label="Spis treści dokumentu">([\s\S]*?)<\/nav>/)[1];
      const anchors = [...toc.matchAll(/href="#([^"]+)"/g)].map(match => match[1]);
      assert.equal(anchors.length, headings, slug);
      for (const id of anchors) assert(page.includes('id="' + id + '"'), id);
      assert.match(page, evidence);
      if (slug === 'polityka-prywatnosci') {
        assert.match(page, /MariaDB[\s\S]+SMTP[\s\S]+OVH[\s\S]+XSRF-TOKEN[\s\S]+Statystyki są domyślnie wyłączone/);
        assert.doesNotMatch(page, /WordPress|Google Analytics|3CX/);
      }
      assert(page.includes('href="/dokumenty/">Wróć do dokumentów'));
      const redirected = await request(oldPath + '?from=old');
      assert.equal(redirected.status, 301);
      assert.equal(redirected.headers.get('location'), path + '?from=old');
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

  await t.test('contact keeps its layout, phone and email with the enabled form', async () => {
    for (const path of ['/kontakt/', '/kontakt/?wyslano=1']) {
      const response = await request(path);
      assert.equal(response.status, 200);
      assert.match(response.headers.get('cache-control'), /no-store/);
      const html = await response.text();
      assert.match(html, /<form class="contact-form" id="formularz"/);
      assert(html.includes('href="tel:' + data.site.phone_href + '"'));
      assert(html.includes('href="mailto:' + data.site.email + '"'));
      for (const field of ['name', 'email', 'phone', 'message']) {
        assert.match(html, new RegExp('<(?:input|textarea)[^>]*name="' + field + '"'));
      }
      assert.match(html, /<button class="button contact-submit" type="submit">/);
      assert.match(html, /name="_token"/);
      assert.doesNotMatch(html, /Wiadomość została przyjęta/);
    }
  });
});

function articleRows(database, rows, replace = false) {
  const operations = replace ? [{ sql: 'DELETE FROM articles' }] : [];
  for (const row of rows) {
    operations.push({
      sql: 'INSERT INTO articles (slug,title,summary,category,body_html,date,status,deleted_at) VALUES (?,?,?,?,?,?,?,?)',
      params: [
        row.slug, row.title ?? row.slug, row.summary ?? null, row.category ?? null,
        row.body_html ?? '<p>Synthetic article.</p>', row.date ?? null, row.status ?? 'published', row.deleted_at ?? null,
      ],
    });
  }
  database.query(operations);
}

test('homepage and archive preserve publication eligibility, ordering and searchable body text', async t => {
  const { origin, database, stop } = await startSite();
  t.after(stop);
  const fixtures = [
    { slug: 'oldest', date: '2023-12-31' },
    { slug: 'previous', date: '2024-01-01' },
    { slug: 'missing-date' },
    { slug: 'newest', date: '2025-06-02', body_html: '<p>Żółwi&nbsp;znacznik</p><p>wyłącznie w treści</p>' },
    { slug: 'leap-day', date: '2024-02-29' },
    { slug: 'same-day', date: '2024-02-29' },
    { slug: 'blank-summary', date: '2026-01-01', summary: ' ' },
    { slug: 'blank-title', date: '2026-02-01', title: ' \t ' },
    { slug: 'another-undated' },
  ].map(entry => ({ title: entry.slug, summary: 'Synthetic preview.', ...entry }));
  articleRows(database, fixtures, true);

  const response = await fetch(origin + '/');
  assert.equal(response.status, 200);
  const html = await response.text();
  const previews = html.match(/<ol class="home-publications-list"[^>]*>([\s\S]*?)<\/ol>/)?.[1];
  assert(previews, 'Homepage publication list is missing');
  assert.deepEqual([...previews.matchAll(/href="\/([^"]+)\/"/g)].map(match => match[1]), ['newest', 'leap-day', 'same-day']);
  assert.deepEqual([...previews.matchAll(/<time datetime="([^"]+)">([^<]+)<\/time>/g)].map(match => match.slice(1)), [
    ['2025-06-02', '02.06.2025'],
    ['2024-02-29', '29.02.2024'],
    ['2024-02-29', '29.02.2024'],
  ]);
  const archive = await fetch(origin + '/artykuly/');
  assert.equal(archive.status, 200);
  const archiveHtml = await archive.text();
  assert.deepEqual([...archiveHtml.matchAll(/href="\/([^"]+)\/" aria-labelledby="publication-title-\d+"/g)].map(match => match[1]),
    ['blank-summary', 'newest', 'leap-day', 'same-day', 'previous', 'oldest', 'missing-date', 'another-undated']);
  assert.match(archiveHtml, /data-publication-search="newest Synthetic preview\. Żółwi znacznik wyłącznie w treści /);
});

test('related publications use the two newest valid entries out of 100', async t => {
  const { origin, database, stop } = await startSite();
  t.after(stop);
  const rows = Array.from({ length: 100 }, (_, index) => ({
    slug: 'fixture-' + index,
    title: 'Fixture article ' + index,
    category: index >= 97 && index <= 98 ? 'Other category' : 'Current category',
    date: new Date(Date.UTC(2024, 0, index + 1)).toISOString().slice(0, 10),
  }));
  const current = rows[99];
  rows[96].slug = 'admin';
  rows[96].date = '2099-01-01';
  rows[95].title = ' \t ';
  rows[95].date = '2098-01-01';

  for (const [dataset, expected] of [
    [[...rows.filter((_, i) => i % 2 === 0), ...rows.filter((_, i) => i % 2 !== 0).reverse()], ['fixture-98', 'fixture-97']],
    [[current, rows[0]], ['fixture-0']],
    [[current], []],
  ]) {
    articleRows(database, dataset, true);
    const response = await fetch(origin + '/' + current.slug + '/');
    assert.equal(response.status, 200);
    const html = await response.text();
    const links = [...html.matchAll(/href="\/([^"]+)\/" aria-labelledby="article-related-\d+"/g)].map(match => match[1]);
    assert.deepEqual(links, expected);
    assert.match(html, /href="\/artykuly\/"/);
    assert.equal(html.includes('class="article-navigation"'), expected.length === 0);
  }
});

test('cached routes serve live edits and hide withdrawn, draft, deleted, and reserved content', async t => {
  const { origin, database, data, stop } = await startSite({ cached: true });
  t.after(stop);
  const marker = 'Ukryta publikacja regresyjna';
  const article = { title: marker, summary: marker, date: '2099-01-01', body_html: '<p>' + marker + '</p>' };
  const hidden = [
    { slug: 'fixture-draft', status: 'draft' },
    { slug: 'fixture-unpublished', status: 'unpublished' },
    { slug: 'fixture-deleted', deleted_at: '2026-11-01 12:00:00' },
    { slug: 'admin' }, { slug: 'livewire' }, { slug: 'up' },
  ];
  const originalSlug = data.articles[0].slug;
  articleRows(database, hidden.map(row => ({ ...article, ...row })));
  const htmlAt = async (path, status = 200) => {
    const response = await fetch(origin + path);
    assert.equal(response.status, status, path);
    return response.text();
  };

  for (const entry of hidden) {
    assert(!(await htmlAt('/' + entry.slug + '/', 404)).includes(marker));
  }
  for (const path of ['/', '/artykuly/', '/' + originalSlug + '/', '/sitemap.xml']) {
    assert(!(await htmlAt(path)).includes(marker));
  }
  const oldSitemap = await htmlAt('/sitemap.xml');
  for (const entry of hidden) assert(!oldSitemap.includes('/' + entry.slug + '/'));

  articleRows(database, [{
    slug: 'fixture-live', title: 'Opublikowana próba Łódź', summary: 'Zażółć gęślą jaźń 😀', date: '2098-01-01',
    body_html: '<h2>Treść</h2><p><a href="/kontakt/">Łącze</a></p><h2>Dane</h2><table><caption>Próba</caption><tr><td>Wartość</td></tr></table>',
  }]);
  assert.match(await htmlAt('/'), /Opublikowana próba Łódź/);
  assert.match(await htmlAt('/artykuly/'), /Opublikowana próba Łódź/);
  assert.match(await htmlAt('/' + originalSlug + '/'), /Opublikowana próba Łódź/);
  const live = await htmlAt('/fixture-live/');
  assert.match(live, /Zażółć gęślą jaźń 😀/);
  assert.match(live, /id="sekcja-tresc"/);
  assert.match(live, /class="article-table-scroll"/);
  assert.match(live, /href="\/kontakt\/">Łącze/);
  assert.match(await htmlAt('/sitemap.xml'), /\/fixture-live\//);

  database.query([{ sql: 'UPDATE articles SET title=? WHERE slug=?', params: ['Zmieniona próba Poznań', 'fixture-live'] }]);
  assert.match(await htmlAt('/fixture-live/'), /Zmieniona próba Poznań/);
  database.query([{ sql: 'UPDATE articles SET status=? WHERE slug=?', params: ['unpublished', 'fixture-live'] }]);
  await htmlAt('/fixture-live/', 404);
  assert.doesNotMatch(await htmlAt('/artykuly/'), /Zmieniona próba Poznań/);
  assert.doesNotMatch(await htmlAt('/sitemap.xml'), /\/fixture-live\//);
  assert.doesNotMatch(await htmlAt('/sitemap.xml'), /\/dokumenty\//);

  database.query([{ sql: 'DROP TABLE articles' }]);
  const outage = await htmlAt('/artykuly/', 503);
  assert.match(outage, /Usługa jest tymczasowo niedostępna/);
  assert.doesNotMatch(outage, /SQLSTATE|SELECT|pdo_mysql|kancelaria_jp_test/);
});

test('specialization order, persisted team groups, and active vacancies are database driven', async t => {
  const { origin, database, data, stop } = await startSite();
  t.after(stop);
  const htmlAt = async (path, status = 200) => {
    const response = await fetch(origin + path);
    assert.equal(response.status, status, path);
    return response.text();
  };
  const homeLinks = html => [...html.matchAll(/class="home-practice-link" href="\/specjalizacje\/([^/]+)\//g)].map(match => match[1]);

  assert.deepEqual(homeLinks(await htmlAt('/')), data.services.slice(0, 8).map(entry => entry.slug));
  const initialHome = await htmlAt('/');
  assert(initialHome.includes(data.team[0].name) && initialHome.includes(data.team[1].name));
  assert(!initialHome.includes(data.team[2].name));
  assert.match(await htmlAt('/kariera/'), /Brak opublikowanych ofert/);

  database.query([
    { sql: 'UPDATE specializations SET active=0 WHERE slug=?', params: [data.services[0].slug] },
    { sql: 'UPDATE specializations SET deleted_at=? WHERE slug=?', params: ['2026-11-01 12:00:00', data.services[1].slug] },
    { sql: 'UPDATE specializations SET display_order=0, homepage_featured=1, title=? WHERE slug=?', params: ['Próba specjalizacji Łódź', data.services[9].slug] },
    { sql: 'UPDATE team_members SET homepage_partner=0 WHERE name=?', params: [data.team[0].name] },
    { sql: 'UPDATE team_members SET homepage_partner=1, team_group=?, name=? WHERE name=?', params: ['support', 'Próba profilu Łódź', data.team[2].name] },
    { sql: 'UPDATE team_members SET deleted_at=? WHERE name=?', params: ['2026-11-01 12:00:00', data.team[3].name] },
    ...[
      ['Próba naboru otwarta', 'open', null],
      ['Nabór utajniony zamknięty', 'closed', null],
      ['Nabór utajniony szkic', 'draft', null],
      ['Nabór utajniony skasowany', 'open', '2026-11-01 12:00:00'],
    ].map(([title,status,deleted], index) => ({
      sql: 'INSERT INTO job_offers (slug,title,description,url,status,deleted_at,display_order) VALUES (?,?,?,?,?,?,?)',
      params: ['oferta-probna-' + index,title,'Opis próby – żółć','https://fixture.example.test/job',status,deleted,index+1],
    })),
  ]);
  const home = await htmlAt('/');
  assert.deepEqual(homeLinks(home), [data.services[9].slug, ...data.services.slice(2, 8).map(entry => entry.slug)]);
  assert.match(home, /Próba profilu Łódź/);
  assert(!home.includes(data.team[0].name));
  for (const entry of data.services.slice(0, 2)) await htmlAt('/specjalizacje/' + entry.slug + '/', 404);
  assert.match(await htmlAt('/specjalizacje/' + data.services[9].slug + '/'), /Próba specjalizacji Łódź/);
  const sitemap = await htmlAt('/sitemap.xml');
  assert(!sitemap.includes('/' + data.services[0].slug + '/') && !sitemap.includes('/' + data.services[1].slug + '/'));

  const team = await htmlAt('/zespol/');
  assert.match(team, /team-group--support[\s\S]*Próba profilu Łódź/);
  assert(!team.includes(data.team[3].name));
  const career = await htmlAt('/kariera/');
  assert.match(career, /Próba naboru otwarta/);
  assert.match(career, /href="\/kariera\/oferta-probna-0\/"/);
  assert.doesNotMatch(career, /fixture\.example\.test/);
  const offer = await htmlAt('/kariera/oferta-probna-0/');
  assert.match(offer, /https:\/\/fixture\.example\.test\/job/);
  assert.match(offer, /rel="canonical" href="[^"]+\/kariera\/oferta-probna-0\/"/);
  assert(sitemap.includes('/kariera/oferta-probna-0/'));
  for (const index of [1, 2, 3]) {
    await htmlAt('/kariera/oferta-probna-' + index + '/', 404);
    assert(!sitemap.includes('/kariera/oferta-probna-' + index + '/'));
  }
  assert.doesNotMatch(career, /Nabór utajniony/);
  database.query([{ sql: 'UPDATE job_offers SET status=? WHERE title=?', params: ['closed', 'Próba naboru otwarta'] }]);
  assert.doesNotMatch(await htmlAt('/kariera/'), /Próba naboru otwarta/);
  assert.match(await htmlAt('/kariera/'), /Brak opublikowanych ofert/);
});
