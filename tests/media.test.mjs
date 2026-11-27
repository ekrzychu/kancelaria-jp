import assert from 'node:assert/strict';
import { randomBytes } from 'node:crypto';
import { access, readFile, readdir } from 'node:fs/promises';
import { request } from 'node:http';
import { join } from 'node:path';
import { test } from 'node:test';
import { startSite, until } from './support/site.mjs';
import { livewire, startBrowser } from './support/chrome.mjs';

test('GD validates and re-encodes JPEG, PNG and WebP from disposable fixtures', async t => {
  const app = await startSite();
  t.after(app.stop);
  assert.match(app.mediaChecks('processor'), /orientation, bounds, transparency, metadata and format rejection passed/);
});

test('managed media uploads, editorial references and delivery respect the article and portrait lifecycle', async t => {
  const app = await startSite({ cached: true });
  t.after(app.stop);
  const browser = await startBrowser();
  t.after(browser.close);
  const { evaluate } = browser;
  const fixture = JSON.parse(app.mediaChecks('fixtures'));
  const sample = (await readFile(fixture.jpeg)).toString('base64');
  const screenshotDir = process.env.MEDIA_SCREENSHOTS;
  const sql = (query, params = []) => app.database.query([{ sql: query, params }])[0];
  const password = 'Aa!' + randomBytes(18).toString('hex');
  const administrator = 'admin@example.test';
  const email = section => section + '@example.test';
  app.panelChecks(['provision'], JSON.stringify({ name: 'Administrator testowy', email: administrator, password }));
  for (const section of ['articles', 'team']) {
    sql('INSERT INTO users (name,email,password,permissions) SELECT ?,?,password,? FROM users WHERE email=?',
      [section, email(section), JSON.stringify([section]), administrator]);
  }

  const visit = path => browser.navigate(app.adminOrigin + path);
  const { wire } = livewire(browser, app);
  const login = async (user, destination) => {
    await browser.call('Network.clearBrowserCookies');
    await visit('/login');
    await wire('Login', 'authenticate', { email: user, password });
    await until(() => evaluate(path => location.pathname === path && document.querySelector('.fi-sidebar-nav'), destination), destination);
  };
  const capture = async name => {
    if (screenshotDir) await browser.screenshot(screenshotDir, name);
  };
  const showImages = async (selector, imagePath) => {
    await until(() => evaluate(selector => document.querySelector(selector) !== null, selector), 'media element');
    await evaluate(selector => document.querySelector(selector).scrollIntoView({ block: 'center' }), selector);
    await until(() => evaluate(prefix => {
      const image = document.querySelector('img[src^="' + prefix + '"]');
      return image?.complete && image.naturalWidth > 0;
    }, imagePath), 'visible media preview');
  };
  const action = async (page, name, id) => {
    await wire(page, 'mountAction', {}, id ? [name, {}, { table: true, recordKey: String(id) }] : [name]);
    await wire(page, 'callMountedAction');
  };
  const publicMedia = id => fetch(app.origin + '/media/' + id, { redirect: 'manual' });
  const privateStatus = id => evaluate(async id => (await fetch('/media-panel/' + id)).status, id);
  const stored = id => access(join(app.dir, 'storage', 'app', 'media', id + '.webp'));
  const meta = id => sql('SELECT * FROM media_images WHERE id=?', [id])[0];

  const rawRequest = (origin, path, { method = 'GET', headers = {} } = {}) => new Promise((resolve, reject) => {
    const { host, port } = new URL(origin);
    const req = request({ hostname: '127.0.0.1', port, path, method, headers: { Host: host, Accept: 'application/json', ...headers } }, res => {
      res.resume();
      resolve(res.statusCode);
    });
    req.on('error', reject);
    req.end();
  });
  const apiUpload = (purpose, contents = sample) => evaluate(async (purpose, contents) => {
    const data = new FormData();
    data.set('image', new Blob([Uint8Array.from(atob(contents), c => c.charCodeAt(0))], { type: 'image/jpeg' }), 'upload.jpg');
    const response = await fetch('/media-panel/upload/' + purpose, {
      method: 'POST', body: data, headers: {
        Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('script[data-csrf]').getAttribute('data-csrf'),
      },
    });
    const result = response.headers.get('content-type')?.includes('application/json') ? await response.json() : {};
    return { status: response.status, id: result.id };
  }, purpose, contents);

  const uploadFile = async (file, index = 0) => {
    const previous = await evaluate(index => [...document.querySelectorAll('input[type=file]')][index]
      .closest('[x-data]').querySelector('img')?.getAttribute('src') ?? null, index);
    const { root } = await browser.call('DOM.getDocument');
    const { nodeIds } = await browser.call('DOM.querySelectorAll', { nodeId: root.nodeId, selector: 'input[type=file]' });
    assert(nodeIds[index], 'image file input');
    await browser.call('DOM.setFileInputFiles', { files: [file], nodeId: nodeIds[index] });
    const src = await until(() => evaluate((index, previous) => {
      const container = [...document.querySelectorAll('input[type=file]')][index].closest('[x-data]');
      const error = container.querySelector('[role=alert]');
      if (error?.getClientRects().length && error.textContent) throw new Error(error.textContent);
      const image = container.querySelector('img');
      const src = image?.getAttribute('src');
      return src && src !== previous && image.complete && image.naturalWidth > 0 ? src : null;
    }, index, previous), 'uploaded image preview');
    return src.split('/').at(-1);
  };
  const clickText = (selector, label, index = 0) => evaluate((selector, label, index) => {
    const button = [...document.querySelectorAll(selector)].filter(node => node.textContent.trim() === label)[index];
    if (!button) throw new Error('Missing control: ' + label);
    button.click();
  }, selector, label, index);

  for (const purpose of ['article', 'portrait', 'avatar']) {
    assert([401, 419].includes(await rawRequest(app.adminOrigin, '/media-panel/upload/' + purpose, { method: 'POST' })));
    assert([404, 405].includes(await rawRequest(app.origin, '/media-panel/upload/' + purpose, { method: 'POST' })));
  }
  assert.equal(await rawRequest(app.origin, '/media/not-an-id'), 404);
  await browser.viewport(1440, 900);
  await visit('/login');
  assert.equal((await apiUpload('article')).status, 401);
  await login(administrator, '/artykuly');
  await visit('/artykuly/dodaj');
  const foreign = await apiUpload('article');
  assert.equal(foreign.status, 201);
  assert.equal(meta(foreign.id).record_id, null);
  await visit('/profile');
  const avatar = await uploadFile(fixture.jpeg);
  await wire('Profile', 'save', { currentPassword: password });
  assert.equal(sql('SELECT avatar_media_id FROM users WHERE email=?', [administrator])[0].avatar_media_id, avatar);
  assert.equal(await privateStatus(avatar), 200);
  assert.equal((await publicMedia(avatar)).status, 404);
  await until(() => evaluate(id => document.querySelector('.fi-topbar img.fi-avatar')?.getAttribute('src') === '/media-panel/' + id, avatar), 'account menu photo');

  await login(email('team'), '/zespol');
  assert.equal(await privateStatus(avatar), 404);
  await visit('/profile');
  const personal = await uploadFile(fixture.jpeg);
  await wire('Profile', 'save', { currentPassword: password });
  assert.equal(await privateStatus(personal), 200);
  const newAvatar = await uploadFile(fixture.png);
  await wire('Profile', 'save', { currentPassword: password });
  assert.equal(await privateStatus(personal), 404);
  assert.equal(await privateStatus(newAvatar), 200);
  await visit('/profile');
  await clickText('button', 'Usuń zdjęcie');
  await wire('Profile', 'save', { currentPassword: password });
  assert.equal(await privateStatus(newAvatar), 404);
  await until(() => evaluate(() => document.querySelector('.fi-topbar img.fi-avatar')?.getAttribute('src').startsWith('data:image/svg+xml,')), 'account menu initials');
  await visit('/zespol/dodaj');
  assert.equal((await apiUpload('article')).status, 403);
  assert.equal(await privateStatus(foreign.id), 404);

  const invalid = (await readFile(fixture.invalid)).toString('base64');
  const pendingBefore = sql('SELECT COUNT(*) AS total FROM media_images')[0].total;
  assert.equal((await apiUpload('portrait', invalid)).status, 422);
  assert.equal(sql('SELECT COUNT(*) AS total FROM media_images')[0].total, pendingBefore);
  const files = () => readdir(join(app.dir, 'storage', 'app', 'media'));
  const beforeFailedWrite = await files();
  sql("CREATE TRIGGER test_reject_media BEFORE INSERT ON media_images FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Synthetic failed media insert'");
  try {
    assert.equal((await apiUpload('portrait')).status, 503);
    assert.equal(sql('SELECT COUNT(*) AS total FROM media_images')[0].total, pendingBefore);
    assert.deepEqual(await files(), beforeFailedWrite);
  } finally {
    sql('DROP TRIGGER test_reject_media');
  }
  const portrait = await uploadFile(fixture.png);
  await capture('desktop-portrait-upload');
  assert.equal(meta(portrait).width, 1200);
  assert.equal(await privateStatus(portrait), 200);
  assert.equal((await publicMedia(portrait)).status, 404);
  const name = 'Profil testowy z portretem';
  const member = () => sql('SELECT * FROM team_members WHERE name=?', [name])[0];
  await wire('CreateTeamMember', 'create', { name, role: 'Stanowisko testowe', team_group: 'support' });
  assert.equal(member().portrait_media_id, portrait);
  assert.equal((await publicMedia(portrait)).headers.get('content-type'), 'image/webp');
  assert.match((await publicMedia(portrait)).headers.get('cache-control'), /no-store/);
  assert((await (await fetch(app.origin + '/zespol/')).text()).includes('/media/' + portrait));
  await visit('/zespol/' + member().id + '/edytuj');
  const replacement = await uploadFile(fixture.jpeg);
  assert.equal((await publicMedia(portrait)).status, 200, 'old association survives until save');
  await wire('EditTeamMember', 'save');
  assert.equal(member().portrait_media_id, replacement);
  assert.equal((await publicMedia(portrait)).status, 404);
  await stored(portrait);
  await visit('/zespol');
  await action('ListTeamMembers', 'delete', member().id);
  assert.equal((await publicMedia(replacement)).status, 404);
  await stored(replacement);
  await action('ListTeamMembers', 'restore', member().id);
  assert.equal((await publicMedia(replacement)).status, 200);
  await visit('/zespol/' + member().id + '/edytuj');
  await clickText('button', 'Usuń zdjęcie');
  await wire('EditTeamMember', 'save');
  assert.equal(member().portrait_media_id, null);
  assert.equal((await publicMedia(replacement)).status, 404);
  await visit('/zespol/dodaj');
  const abandoned = await apiUpload('portrait');
  assert.equal(abandoned.status, 201);
  assert.equal(meta(abandoned.id).record_id, null);
  const teamCookie = (await browser.call('Network.getCookies', { urls: [app.adminOrigin] })).cookies
    .map(value => value.name + '=' + value.value).join('; ');

  await login(email('articles'), '/artykuly');
  await visit('/artykuly/dodaj');
  assert.equal((await apiUpload('portrait')).status, 403);
  assert.equal(await privateStatus(foreign.id), 404);
  const slug = 'test-zdjec-artykulu';
  const article = () => sql('SELECT * FROM articles WHERE slug=?', [slug])[0];
  const draft = { title: 'Test zdjęć artykułu', slug, body_0: '<p>Akapit testowy</p><img src="https://example.test/tracker.png">' };
  const block = (id, alt, caption, position = 10000) => ({ image: id, alt, caption, position });
  await clickText('button', 'Dodaj zdjęcie');
  await until(() => evaluate(() => document.querySelectorAll('input[type=file]').length === 1), 'first image field');
  const first = await uploadFile(fixture.webp);
  await clickText('button', 'Dodaj zdjęcie');
  await until(() => evaluate(() => document.querySelectorAll('input[type=file]').length === 2), 'second image field');
  const second = await uploadFile(fixture.jpeg, 1);
  assert.equal(meta(first).width, 1920);
  await wire('CreateArticle', 'create', { ...draft, image_blocks: [
    block(first, 'Pierwszy opis', 'Pierwszy podpis'), block(foreign.id, 'Cudzy obraz', ''),
  ] });
  assert.equal(article(), undefined);
  assert.equal(meta(first).record_id, null, 'failed save rolls back image association');
  await wire('CreateArticle', 'create', { ...draft, image_blocks: [
    block(first, 'Pierwszy opis', 'Pierwszy podpis'), block(second, 'Drugi opis', 'Drugi podpis'),
  ] });
  assert(article());
  const id = article().id;
  const edit = '/artykuly/' + id + '/edytuj';
  assert.doesNotMatch(article().body_html, /tracker\.png|<img/);
  assert.equal((await publicMedia(second)).status, 404);
  await visit('/podglad/artykuly/' + id);
  assert(await evaluate(id => document.querySelector('figure img[src="/media-panel/' + id + '"]').alt === 'Drugi opis', second));
  assert(await evaluate(() => document.querySelector('.article-body').innerText.includes('Drugi podpis')));
  await showImages('.article-body figure', '/media-panel/');
  await capture('desktop-private-article-preview');

  await visit(edit);
  assert(await evaluate(() => document.querySelectorAll('input[type=file]').length === 2));
  await showImages('img[src^="/media-panel/"]', '/media-panel/');
  assert(await evaluate(() => document.querySelector('img.panel-image-preview').getBoundingClientRect().height <= 192));
  await capture('desktop-article-images');
  await action('EditArticle', 'publish');
  assert.equal(article().status, 'published');
  assert.equal((await publicMedia(first)).status, 200);
  await browser.navigate(app.origin + '/' + slug + '/');
  assert(await evaluate(id => Boolean(document.querySelector('figure img[src="/media/' + id + '"]')), first));
  await showImages('.article-body figure', '/media/');
  await capture('desktop-public-article-images');
  await visit(edit);
  const third = await uploadFile(fixture.png, 0);
  await wire('EditArticle', 'save', { image_blocks: [
    block(second, 'Drugi opis przed tekstem', 'Drugi podpis', 0), block(third, 'Zastąpiony obraz', 'Nowy podpis'),
    block(third, 'To samo zdjęcie', 'Podpis powtórzenia'),
  ] });
  const publicPage = () => fetch(app.origin + '/' + slug + '/').then(response => response.text());
  const reordered = await publicPage();
  assert(reordered.indexOf('Drugi podpis') < reordered.indexOf('Akapit testowy'));
  assert(!reordered.includes('/media/' + first));
  assert.equal((await publicMedia(first)).status, 404);
  await stored(first);
  await visit(edit);
  await wire('EditArticle', 'save', { image_blocks: [
    block(second, 'Drugi opis przed tekstem', 'Drugi podpis', 0), block(third, 'Zastąpiony obraz', 'Nowy podpis'),
  ] });
  assert.equal((await publicMedia(third)).status, 200, 'remaining reference keeps image visible');

  await browser.viewport(390, 844, true);
  await visit(edit);
  assert(await evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth));
  await showImages('img[src^="/media-panel/"]', '/media-panel/');
  await capture('mobile-article-images');
  await action('EditArticle', 'unpublish');
  assert.equal((await publicMedia(third)).status, 404);
  assert.equal(await privateStatus(third), 200);
  await visit('/artykuly');
  await action('ListArticles', 'delete', id);
  assert.equal(await privateStatus(third), 404);
  await stored(third);
  await action('ListArticles', 'restore', id);
  assert.equal(await privateStatus(third), 200);
  assert.equal((await publicMedia(third)).status, 404);

  sql("UPDATE media_images SET created_at='2000-01-01 00:00:00' WHERE id=?", [foreign.id]);
  assert.match(app.artisan(['media:clean-temporary', '--no-ansi']), /1 expired upload/);
  await stored(foreign.id);
  assert.match(app.artisan(['media:clean-temporary', '--apply', '--no-ansi']), /1 expired upload/);
  assert.equal(meta(foreign.id), undefined);
  await assert.rejects(stored(foreign.id), { code: 'ENOENT' });
  await stored(third);

  await visit(edit);
  const pending = await apiUpload('article');
  assert.equal(pending.status, 201);
  const csrf = await evaluate(() => document.querySelector('script[data-csrf]').getAttribute('data-csrf'));
  const cookie = (await browser.call('Network.getCookies', { urls: [app.adminOrigin] })).cookies
    .map(value => value.name + '=' + value.value).join('; ');
  assert.equal(await rawRequest(app.adminOrigin, '/media-panel/upload/article', {
    method: 'POST', headers: { Cookie: cookie, Origin: app.adminOrigin },
  }), 419);
  await browser.viewport(1440, 900);
  await login(administrator, '/artykuly');
  const employeeId = sql('SELECT id FROM users WHERE email=?', [email('articles')])[0].id;
  await visit('/uzytkownicy/' + employeeId + '/edytuj');
  await wire('EditUser', 'save', { permissions: [] });
  assert.equal(await rawRequest(app.adminOrigin, '/media-panel/upload/article', {
    method: 'POST', headers: { Cookie: cookie, 'X-CSRF-TOKEN': csrf, Origin: app.adminOrigin },
  }), 403);
  assert.equal(await rawRequest(app.adminOrigin, '/media-panel/' + pending.id, { headers: { Cookie: cookie } }), 401);
  await visit('/uzytkownicy');
  await action('ListUsers', 'deactivate', sql('SELECT id FROM users WHERE email=?', [email('team')])[0].id);
  assert.equal(await rawRequest(app.adminOrigin, '/media-panel/' + abandoned.id, { headers: { Cookie: teamCookie } }), 403);
});
