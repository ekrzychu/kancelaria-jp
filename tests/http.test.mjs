import assert from 'node:assert/strict';
import { mkdir, readFile, rm, writeFile } from 'node:fs/promises';
import { join } from 'node:path';
import { test } from 'node:test';
import { routes, startSite } from './support/site.mjs';

test('routing and enquiry workflow use a disposable copy', async t => {
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

  await t.test('unknown routes are served as noindex 404s', async () => {
    for (const path of ['/missing-regression-route/', '/specjalizacje/missing-regression-route/', '/dokumenty/missing-regression-route/']) {
      const result = await request(path);
      assert.equal(result.status, 404);
      assert.match(await result.text(), /<meta name="robots" content="noindex,follow">/);
    }
  });

  await t.test('existing redirects and query string are preserved', async () => {
    for (const [from, to] of [
      ['/aktualnosci/', '/artykuly/'],
      ['/artykuly?rok=2026&sort=oldest', '/artykuly/?rok=2026&sort=oldest'],
      ...paths.filter(path => path !== '/').map(path => [path.slice(0, -1), path]),
    ]) {
      const result = await request(from);
      assert.equal(result.status, 301, from);
      assert.equal(result.headers.get('location'), to);
    }
  });

  await t.test('sitemap matches the current indexable routes', async () => {
    const response = await request('/sitemap.xml');
    assert.equal(response.status, 200);
    const urls = [...(await response.text()).matchAll(/<loc>([^<]+)<\/loc>/g)].map(m => m[1]);
    assert.deepEqual(urls.sort(), paths.filter(path => !path.startsWith('/dokumenty/')).map(path => canonical + path).sort());
  });

  const page = await request('/kontakt/');
  const cookie = page.headers.get('set-cookie')?.split(';')[0];
  const csrf_token = (await page.text()).match(/name="csrf_token" value="([a-f0-9]{64})"/)?.[1];
  assert.ok(cookie && csrf_token, 'Isolated contact session did not initialize');
  const sample = { csrf_token, name: 'Synthetic Example', email: 'fixture@example.test', phone: '', message: 'Synthetic regression test only.' };
  const storage = join(dir, 'storage');
  const submissions = join(storage, 'contact-submissions.jsonl');
  const post = overrides => request('/kontakt/', {
    method: 'POST', headers: { cookie }, body: new URLSearchParams({ ...sample, ...overrides }),
  });

  for (const [name, overrides, expected] of [
    ['required fields', { name: '', email: '', message: '' }, /Uzupełnij imię/],
    ['invalid email', { email: 'invalid-email' }, /Podaj poprawny adres e-mail/],
    ['name character limit', { name: 'ą'.repeat(121) }, /Skróć dane/],
    ['email character limit', { email: 'a'.repeat(64) + '@' + 'b'.repeat(60) + '.' + 'c'.repeat(60) + '.test' }, /Skróć dane/],
    ['phone character limit', { phone: '0'.repeat(41) }, /Skróć dane/],
    ['message character limit', { message: 'a'.repeat(4001) }, /Skróć dane/],
    ['bad CSRF token', { csrf_token: 'invalid' }, /Sesja formularza wygasła/],
  ]) {
    await t.test(name, async () => {
      const response = await post(overrides);
      assert.equal(response.status, 200);
      assert.match(await response.text(), expected);
      await assert.rejects(readFile(submissions), { code: 'ENOENT' });
    });
  }

  await t.test('honeypot uses the normal redirect and discards the record', async () => {
    const response = await post({ website: 'fixture' });
    assert.equal(response.status, 303);
    assert.equal(response.headers.get('location'), '/kontakt/?wyslano=1');
    await assert.rejects(readFile(submissions), { code: 'ENOENT' });
  });

  await t.test('persists a valid enquiry without a phone and confirms once', async () => {
    const response = await post({});
    assert.equal(response.status, 303);
    const location = response.headers.get('location');
    assert.equal(location, '/kontakt/?wyslano=1');
    const [line, extra] = (await readFile(submissions, 'utf8')).trim().split(/\r?\n/);
    const { created_at, ...record } = JSON.parse(line);
    assert(!Number.isNaN(Date.parse(created_at)));
    assert.equal(extra, undefined);
    assert.deepEqual(record, { name: sample.name, email: sample.email, phone: '', message: sample.message });
    assert.match(await (await request(location, { headers: { cookie } })).text(), /Wiadomość została zapisana/);
    assert.doesNotMatch(await (await request(location, { headers: { cookie } })).text(), /Wiadomość została zapisana/);
  });

  await t.test('limits subsequent submissions without another append', async () => {
    for (let i = 0; i < 2; i++) assert.equal((await post({})).status, 303);
    const response = await post({});
    assert.equal(response.status, 200);
    assert.match(await response.text(), /Otrzymaliśmy zbyt wiele zgłoszeń/);
    assert.equal((await readFile(submissions, 'utf8')).trim().split(/\r?\n/).length, 3);
  });

  await t.test('storage failure returns an error and no success redirect', async () => {
    await rm(join(storage, 'contact-rate-limit.json'));
    await rm(submissions);
    await mkdir(submissions); // A directory at this path makes the append fail on all tested platforms.
    const response = await post({});
    assert.equal(response.status, 200);
    assert.equal(response.headers.get('location'), null);
    assert.match(await response.text(), /Nie udało się zapisać wiadomości/);
  });
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
