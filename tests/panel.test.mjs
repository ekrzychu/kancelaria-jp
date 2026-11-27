import assert from 'node:assert/strict';
import { createHash, randomBytes } from 'node:crypto';
import { request } from 'node:http';
import { test } from 'node:test';
import { livewire, startBrowser } from './support/chrome.mjs';
import { startSite, until } from './support/site.mjs';

function http(app, path, { admin = true, method = 'GET', headers = {}, body } = {}) {
  return new Promise((resolve, reject) => {
    const origin = new URL(admin ? app.adminOrigin : app.origin);
    const req = request({
      hostname: '127.0.0.1', port: origin.port, path, method, headers: { Host: origin.host, ...headers },
    }, res => {
      res.setEncoding('utf8');
      let text = '';
      res.on('data', chunk => { text += chunk; });
      res.on('end', () => resolve({ status: res.statusCode, headers: res.headers, text }));
    });
    req.on('error', reject);
    req.end(body);
  });
}

const rows = (app, sql, params = []) => app.database.query([{ sql, params }])[0];
const randomPassword = () => 'Aa!' + randomBytes(18).toString('hex');
const screenshotDir = process.env.PANEL_SCREENSHOTS;

test('hostname boundaries, cookies, CSRF, recovery origin and cached routes', async t => {
  const app = await startSite({ cached: true });
  t.after(app.stop);
  const login = await http(app, '/login');
  assert.equal(login.status, 200, login.text.slice(0, 200));
  assert.match(login.text, /Jaroch-Konwent Pakos/);
  assert.match(login.headers['cache-control'], /no-store/);
  assert.match(login.headers['x-robots-tag'], /noindex/);
  const cookies = login.headers['set-cookie'];
  const session = cookies.find(cookie => cookie.startsWith('kjp_panel_session='));
  assert.match(session, /httponly/i);
  assert.match(session, /samesite=lax/i);
  assert.doesNotMatch(session, /domain=|;\s*secure/i);
  assert.equal((await http(app, '/')).headers.location, app.adminOrigin + '/login');
  const publicHome = await http(app, '/', { admin: false, headers: { 'X-Forwarded-Host': new URL(app.adminOrigin).host } });
  assert.equal(publicHome.status, 200);
  assert(!publicHome.headers['set-cookie']);
  assert(!publicHome.text.includes('/css/filament/'));
  for (const path of ['/admin', '/admin/', '/login', '/profile', '/password-reset/request', '/uzytkownicy',
    '/artykuly/dodaj', '/artykuly/1/edytuj', '/podglad/artykuly/1']) {
    assert.equal((await http(app, path, { admin: false })).status, 404, path);
  }
  for (const path of ['/o-kancelarii/', '/kontakt/', '/admin', '/sitemap.xml']) {
    assert.equal((await http(app, path)).status, 404, path);
  }
  assert.equal((await http(app, '/login', { headers: { Host: 'unknown.invalid' } })).status, 400);
  assert.equal((await http(app, '/login', { headers: { 'X-Forwarded-Host': 'unknown.invalid', 'X-Forwarded-Proto': 'https' } })).status, 200);
  const update = new URL(login.text.match(/data-update-uri="([^"]+)"/)?.[1], app.adminOrigin);
  assert.equal(update.origin, app.adminOrigin);
  const endpoint = update.pathname;
  assert.match(endpoint, /^\/livewire-[a-f0-9]+\/update$/);
  const payload = { method: 'POST', headers: { 'X-Livewire': '', 'Content-Type': 'application/json',
    Cookie: cookies.map(cookie => cookie.split(';')[0]).join('; ') }, body: '{}' };
  assert.equal((await http(app, endpoint, { ...payload, admin: false })).status, 404);
  assert.equal((await http(app, endpoint, { ...payload, headers: { ...payload.headers, Host: 'other.invalid' } })).status, 400);
  assert.equal((await http(app, endpoint, { ...payload, headers: { ...payload.headers, Origin: app.origin } })).status, 403);
  assert.equal((await http(app, endpoint, payload)).status, 419);
  assert.equal((await http(app, endpoint.replace('update', 'upload-file'), payload)).status, 404);
  assert.equal((await http(app, '/filament/exports/1/download')).status, 404);
  assert.equal((await http(app, '/password-reset/request')).status, 200);
  app.panelChecks(['provision'], JSON.stringify({ name: 'Test admin', email: 'admin@example.test', password: randomPassword() }));
  assert.equal(app.panelChecks(['recovery-origin']), app.adminOrigin + '/password-reset/reset');
});

test('administrators and employees manage accounts and publications in the browser', async t => {
  const app = await startSite();
  t.after(app.stop);
  const browser = await startBrowser();
  t.after(browser.close);
  const { evaluate } = browser;
  const visit = path => browser.navigate(app.adminOrigin + path);
  const adminEmail = 'admin@example.test';
  const employeeEmail = 'employee@example.test';
  const adminPassword = randomPassword();
  const employeePassword = randomPassword();
  const newPassword = randomPassword();
  const slug = 'syntetyczna-publikacja';
  const title = 'Syntetyczna publikacja kontrolna';
  app.panelChecks(['provision'], JSON.stringify({ name: 'Test admin', email: adminEmail, password: adminPassword }));

  const { ready: wireReady, wire } = livewire(browser, app);
  const waitPath = path => until(() => evaluate(path => location.pathname === path, path), path);
  const waitText = value => until(() => evaluate(value => document.body.innerText.includes(value), value), value);
  const login = async (email, password, expectedPath = '/artykuly') => {
    await visit('/login');
    await wire('.login', 'authenticate', { email, password });
    await waitPath(expectedPath);
    if (expectedPath === '/artykuly') await wireReady('ListArticles');
  };
  const logout = async () => {
    await until(() => evaluate(() => document.querySelector('form[action$="/logout"]') !== null), 'account menu logout form');
    await evaluate(() => {
      document.querySelector('form[action$="/logout"]').requestSubmit();
    });
    await waitPath('/login');
    await wireReady('Login');
  };
  const account = email => rows(app, 'SELECT * FROM users WHERE email=?', [email])[0];
  const article = () => rows(app, 'SELECT * FROM articles WHERE slug=?', [slug])[0];
  const tableAction = async (page, name, id) => {
    await wire(page, 'mountAction', {}, [name, {}, { table: true, recordKey: String(id) }]);
    await wire(page, 'callMountedAction');
  };

  await browser.viewport(1440, 900);
  await visit('/login');
  await wire('.login', 'authenticate', { email: adminEmail, password: randomPassword() });
  await waitText('Błędny login lub hasło.');
  assert.equal(account(adminEmail).last_login_at, null);
  await login(adminEmail, adminPassword);
  assert(account(adminEmail).last_login_at);
  const cookies = (await browser.call('Network.getCookies', { urls: [app.adminOrigin, app.origin] })).cookies;
  assert.equal(cookies.find(cookie => cookie.name === 'kjp_panel_session').domain, 'panel.localhost');
  assert(!(await browser.call('Network.getCookies', { urls: [app.origin] })).cookies.some(cookie => cookie.name === 'kjp_panel_session'));
  await visit('/uzytkownicy');
  await waitText('Dodaj użytkownika');
  if (screenshotDir) await browser.screenshot(screenshotDir, 'desktop-users');

  await tableAction('.list-users', 'deactivate', account(adminEmail).id);
  assert.equal(Number(account(adminEmail).is_active), 1);
  await visit('/uzytkownicy/dodaj');
  await wire('.create-user', 'create', {
    name: 'Test employee', email: employeeEmail, password: employeePassword, password_confirmation: employeePassword,
    is_admin: '0', permissions: ['articles'],
  });
  await waitPath('/uzytkownicy');
  assert.deepEqual(JSON.parse(account(employeeEmail).permissions), ['articles']);
  await logout();

  await login(employeeEmail, employeePassword);
  assert(!await evaluate(() => [...document.querySelectorAll('.fi-sidebar-item-label')]
    .some(node => ['Użytkownicy', 'Wiadomości', 'Zespół', 'Kariera', 'Specjalizacje'].includes(node.textContent.trim()))));
  for (const path of ['/uzytkownicy', '/specjalizacje', '/zespol', '/kariera', '/wiadomosci']) {
    assert.equal(await evaluate(async path => (await fetch(path)).status, path), 403, path);
  }

  await visit('/artykuly/dodaj');
  await waitText('Zapisz szkic');
  await wire('.create-article', 'create', { title, slug: 'login', body_0: '<p>Tekst próbny</p>' });
  assert.equal(rows(app, 'SELECT COUNT(*) AS count FROM articles')[0].count, 3);
  const unsafe = '<h2>Test nagłówka</h2><p>Treść <strong>pogrubiona</strong> <a href="javascript:alert(1)">link</a></p>'
    + '<img src="/assets/img/podzial-majatku-1.png" onerror="alert(1)"><script>alert(1)</script><iframe src="https://example.test"></iframe>'
    + '<table><tbody><tr><td>Komórka</td></tr></tbody></table>';
  await wire('.create-article', 'create', { title, slug, date: '2035-02-01', summary: 'Streszczenie kontrolne.', body_0: unsafe });
  await until(() => article()?.status === 'draft', 'saved draft');
  assert.doesNotMatch(article().body_html, /script|onerror|iframe|javascript:/i);
  assert.match(article().body_html, /<strong>pogrubiona<\/strong>/);
  assert.match(article().body_html, /<table>.*Komórka.*<\/table>/s);
  const id = article().id;
  assert.equal((await http(app, '/' + slug + '/', { admin: false })).status, 404);
  const previewPath = '/podglad/artykuly/' + id;
  assert.equal((await http(app, previewPath, { admin: false })).status, 404);
  assert.equal((await http(app, previewPath)).status, 302);
  await visit(previewPath);
  await waitText(title);
  assert(await evaluate(origin => document.querySelector('.publication-back').href.startsWith(origin), app.origin));
  assert(!await evaluate(() => document.querySelector('.article-body script, .article-body [onerror], .article-body iframe')));
  if (screenshotDir) await browser.screenshot(screenshotDir, 'desktop-private-preview');

  const editPath = '/artykuly/' + id + '/edytuj';
  assert.equal((await http(app, editPath)).status, 302);
  assert.equal((await http(app, editPath, { admin: false })).status, 404);
  const editorAction = async (action, data = {}) => {
    await wire('.edit-article', 'mountAction', data, [action]);
    await wire('.edit-article', 'callMountedAction');
  };
  const rowActions = () => evaluate(title => {
    const row = [...document.querySelectorAll('tbody tr')].find(row => row.textContent.includes(title));
    return [...(row?.querySelectorAll('td:last-child :is(a, button)') ?? [])]
      .filter(action => action.getClientRects().length)
      .map(action => action.textContent.trim());
  }, title);
  await visit('/artykuly');
  assert.deepEqual((await rowActions()).filter(Boolean), ['Edytuj', 'Podgląd', 'Opublikuj', 'Więcej']);
  if (screenshotDir) await browser.screenshot(screenshotDir, 'desktop-publication-table');
  await tableAction('.list-articles', 'publish', id);
  assert.equal(article().status, 'published');
  assert((await rowActions()).includes('Wycofaj publikację'));
  for (const path of ['/' + slug + '/', '/artykuly/', '/sitemap.xml', '/', '/' + app.data.articles[0].slug + '/']) {
    const published = await http(app, path, { admin: false });
    assert.equal(published.status, 200, path);
    assert(published.text.includes(slug), path);
  }
  await visit(editPath);
  await editorAction('unpublish', { title: title + ' niezapisany' });
  assert.equal(article().status, 'unpublished');
  assert.equal(article().title, title);
  assert.equal((await http(app, '/' + slug + '/', { admin: false })).status, 404);
  await waitText('Opublikuj ponownie');
  if (screenshotDir) {
    await until(() => evaluate(() => document.querySelector('.tiptap.ProseMirror') !== null), 'rich editor mounted');
    await browser.screenshot(screenshotDir, 'desktop-editor-republish');
  }
  await editorAction('publish', { title: '' });
  assert.equal(article().status, 'unpublished');
  assert.equal(article().title, title);
  assert(await evaluate(() => document.querySelector('[id="form.title"][aria-invalid="true"], [id="data.title"][aria-invalid="true"]') !== null));
  await editorAction('publish', { title: title + ' z edytora', summary: 'Zapisano przy publikacji.' });
  assert.equal(article().status, 'published');
  assert.equal(article().title, title + ' z edytora');
  assert.equal(article().summary, 'Zapisano przy publikacji.');
  assert.equal(article().date, '2035-02-01');
  await waitText('Wycofaj publikację');
  await wire('.edit-article', 'save', { title: title + ' po zmianie', slug: 'niepoprawna-zmiana-adresu' });
  assert.equal(article().title, title + ' po zmianie');
  assert.equal(article().slug, slug);
  if (screenshotDir) {
    await visit('/artykuly/' + id + '/edytuj');
    await until(() => evaluate(() => document.querySelector('.tiptap.ProseMirror') !== null), 'rich editor mounted');
    await browser.screenshot(screenshotDir, 'desktop-article-editor');
  }

  // Each imported record passes through the actual Filament edit form and Tiptap markup.
  const roundTripDifferences = [];
  for (const imported of rows(app, 'SELECT id,slug,title FROM articles WHERE id <> ? ORDER BY id', [id])) {
    const path = '/' + imported.slug + '/';
    const before = await http(app, path, { admin: false });
    await visit('/artykuly/' + imported.id + '/edytuj');
    await until(() => evaluate(() => document.querySelector('.tiptap.ProseMirror') !== null), 'rich editor mounted');
    await wire('.edit-article', 'save', { title: imported.title + ' — test' });
    const after = await http(app, path, { admin: false });
    const markup = text => text.split('<div class="article-body">')[1].split('<section class="article-related"')[0]
      .trim().replace(/>\s+</g, '><');
    const digest = text => createHash('sha256').update(markup(text)).digest('hex');
    if (digest(before.text) !== digest(after.text)) {
      const prior = markup(before.text);
      const next = markup(after.text);
      const index = [...prior].findIndex((letter, i) => letter !== next[i]);
      roundTripDifferences.push({
        slug: imported.slug,
        expected: prior.slice(Math.max(0, index - 15), index + 90),
        actual: next.slice(Math.max(0, index - 15), index + 90),
      });
    }
  }
  assert.deepEqual(roundTripDifferences, []);

  await visit('/artykuly');
  await tableAction('.list-articles', 'unpublish', id);
  assert.equal(article().status, 'unpublished');
  assert((await rowActions()).includes('Opublikuj ponownie'));
  assert.equal((await http(app, '/' + slug + '/', { admin: false })).status, 404);
  assert(!(await http(app, '/sitemap.xml', { admin: false })).text.includes(slug));
  await tableAction('.list-articles', 'delete', id);
  assert(article().deleted_at);
  assert.equal(await evaluate(async path => (await fetch(path)).status, previewPath), 404);
  await tableAction('.list-articles', 'restore', id);
  assert.equal(article().deleted_at, null);
  assert.equal(article().status, 'unpublished');

  await browser.viewport(390, 844, true);
  await visit('/profile');
  assert(await evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth));
  if (screenshotDir) await browser.screenshot(screenshotDir, 'mobile-profile');
  await wire('.profile', 'save', { name: 'Niewłaściwa zmiana', currentPassword: randomPassword() });
  assert.equal(account(employeeEmail).name, 'Test employee');

  await wire('.profile', 'save', {
    name: 'Test employee updated', currentPassword: employeePassword, password: newPassword, passwordConfirmation: newPassword,
    email: 'other@example.test', is_admin: true, permissions: ['messages'], is_active: false,
  });
  assert.equal(account(employeeEmail).name, 'Test employee updated');
  assert.equal(Number(account(employeeEmail).is_admin), 0);
  assert.equal(Number(account(employeeEmail).is_active), 1);
  assert.deepEqual(JSON.parse(account(employeeEmail).permissions), ['articles']);
  await logout();
  await login(employeeEmail, newPassword);
  await visit('/artykuly');
  await wireReady('ListArticles');
  const employeeCookies = (await browser.call('Network.getCookies', { urls: [app.adminOrigin] })).cookies;
  const existingPage = await evaluate(() => ({
    snapshot: [...document.querySelectorAll('[wire\\:snapshot]')]
      .map(el => el.getAttribute('wire:snapshot'))
      .find(snapshot => JSON.parse(snapshot).memo.name.endsWith('\\ListArticles')),
    token: document.querySelector('script[data-update-uri]').getAttribute('data-csrf'),
    endpoint: new URL(document.querySelector('script[data-update-uri]').getAttribute('data-update-uri'), location.href).pathname,
  }));
  await browser.call('Network.clearBrowserCookies');
  await browser.viewport(1440, 900);
  await login(adminEmail, adminPassword);
  await visit('/uzytkownicy');
  await tableAction('.list-users', 'deactivate', account(employeeEmail).id);
  assert.equal(Number(account(employeeEmail).is_active), 0);
  const stale = employeeCookies.map(({ name, value }) => name + '=' + value).join('; ');
  assert(existingPage.snapshot);
  const staleUpdate = await http(app, existingPage.endpoint, {
    method: 'POST', headers: {
      Cookie: stale, Origin: app.adminOrigin, 'X-Livewire': '', 'Content-Type': 'application/json',
    },
    body: JSON.stringify({ _token: existingPage.token, components: [
      { snapshot: existingPage.snapshot, updates: {}, calls: [
        { path: '', method: 'mountAction', params: ['publish', {}, { table: true, recordKey: String(id) }] },
      ] },
    ] }),
  });
  assert.equal(staleUpdate.status, 403);
  assert.equal(article().status, 'unpublished');
  await tableAction('.list-users', 'reactivate', account(employeeEmail).id);
  assert.equal(Number(account(employeeEmail).is_active), 1);
  await visit('/uzytkownicy/' + account(employeeEmail).id + '/edytuj');
  await wire('.edit-user', 'save', {
    name: 'Test employee only team', permissions: ['team'],
  });
  await waitPath('/uzytkownicy');
  assert.deepEqual(JSON.parse(account(employeeEmail).permissions), ['team']);
});

test('concurrent deactivations leave an active administrator', async t => {
  const app = await startSite();
  t.after(app.stop);
  app.panelChecks(['provision'], JSON.stringify({ name: 'First', email: 'first@example.test', password: randomPassword() }));
  rows(app, 'INSERT INTO users (name,email,password,is_admin) SELECT ?,?,password,1 FROM users WHERE id=1',
    ['Second', 'second@example.test']);
  const start = ((Date.now() + 1200) / 1000).toString();
  const outcomes = await Promise.all([
    app.concurrentPanelCheck(['deactivate', '1', '2', start]),
    app.concurrentPanelCheck(['deactivate', '2', '1', start]),
  ]);
  assert.deepEqual(outcomes.map(result => result.stdout).sort(), ['Changed', 'Denied']);
  assert.equal(Number(rows(app, 'SELECT COUNT(*) AS count FROM users WHERE is_active AND is_admin')[0].count), 1);
});

test('inactive accounts cannot log in and rate limits survive a fresh browser session', async t => {
  const app = await startSite();
  t.after(app.stop);
  const browser = await startBrowser();
  t.after(browser.close);
  const email = 'inactive@example.test';
  const password = randomPassword();
  app.panelChecks(['provision'], JSON.stringify({ name: 'Test account', email, password }));
  rows(app, 'UPDATE users SET is_active=0 WHERE email=?', [email]);

  for (let attempt = 1; attempt <= 6; attempt++) {
    await browser.call('Network.clearBrowserCookies');
    await browser.navigate(app.adminOrigin + '/login');
    if (attempt === 6) rows(app, 'UPDATE users SET is_active=1 WHERE email=?', [email]);
    await browser.evaluate(async (email, password) => {
      const page = [...document.querySelectorAll('[wire\\:snapshot]')]
        .find(el => JSON.parse(el.getAttribute('wire:snapshot')).memo.name.endsWith('\\Login'));
      const wire = window.Livewire.find(page.getAttribute('wire:id'));
      await wire.$set('data.email', email, false);
      await wire.$set('data.password', password, false);
      await wire.$call('authenticate');
    }, email, password);
    const message = attempt < 6 ? 'Błędny login lub hasło.' : 'Za dużo nieudanych prób logowania';
    try {
      await until(() => browser.evaluate(message => document.body.innerText.includes(message), message), message);
    } catch (error) {
      const state = await browser.evaluate(() => ({ path: location.pathname, text: document.body.innerText.slice(0, 250) }));
      throw new Error(error.message + ' ' + JSON.stringify(state) + ' counter=' + app.panelChecks(['login-attempts']));
    }
    assert.equal(await browser.evaluate(() => location.pathname), '/login');
  }
  assert.equal(rows(app, 'SELECT last_login_at FROM users WHERE email=?', [email])[0].last_login_at, null);
  assert.equal(Number(app.panelChecks(['login-attempts'])), 5);
});
