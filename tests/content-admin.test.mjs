import assert from 'node:assert/strict';
import { randomBytes } from 'node:crypto';
import { copyFile, readFile, writeFile } from 'node:fs/promises';
import { request } from 'node:http';
import { join } from 'node:path';
import { test } from 'node:test';
import { livewire, startBrowser } from './support/chrome.mjs';
import { startSite, until } from './support/site.mjs';

test('section employees manage specializations, public profiles and recruitment with cached Laravel routes', async t => {
  const portrait = 'synthetic-portrait.webp';
  const app = await startSite({ cached: true, async prepare(dir) {
    const config = join(dir, 'config', 'site.php');
    const images = join(dir, 'public', 'assets', 'img');
    await copyFile(join(images, 'office-reception-800.webp'), join(images, portrait));
    await writeFile(config, (await readFile(config, 'utf8'))
      .replace("'team_portraits' => [],", "'team_portraits' => ['" + portrait + "' => 'Portret testowy'],"));
  } });
  t.after(app.stop);
  const browser = await startBrowser();
  t.after(browser.close);
  const { evaluate } = browser;
  const sql = (query, params = []) => app.database.query([{ sql: query, params }])[0];
  const password = 'Aa!' + randomBytes(18).toString('hex');
  const administrator = 'admin@example.test';
  const sections = { specializations: '/specjalizacje', team: '/zespol', career: '/kariera' };
  const screenshots = process.env.CONTENT_ADMIN_SCREENSHOTS;
  app.panelChecks(['provision'], JSON.stringify({ name: 'Administrator testowy', email: administrator, password }));
  for (const permission of Object.keys(sections)) {
    sql('INSERT INTO users (name,email,password,permissions) SELECT ?,?,password,? FROM users WHERE email=?',
      [permission, permission + '@example.test', JSON.stringify([permission]), administrator]);
  }

  const visit = path => browser.navigate(app.adminOrigin + path);
  const { wire } = livewire(browser, app);
  const html = async (path, status = 200) => {
    const response = await fetch(app.origin + path, { redirect: 'manual' });
    assert.equal(response.status, status, path);
    return response.text();
  };
  const action = async (page, name, record, data = {}) => {
    await wire(page, 'mountAction', data, record ? [name, {}, { table: true, recordKey: String(record.id) }] : [name]);
    await wire(page, 'callMountedAction');
  };
  const capture = async name => {
    assert(await evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth), name);
    if (screenshots) await browser.screenshot(screenshots, name);
  };
  const login = async (email, start) => {
    await browser.call('Network.clearBrowserCookies');
    await visit('/login');
    await wire('Login', 'authenticate', { email, password });
    await until(() => evaluate(path => location.pathname === path && document.querySelector('.fi-sidebar-nav') !== null, start), start);
  };
  const employee = async permission => {
    await login(permission + '@example.test', sections[permission]);
    const links = await evaluate(() => [...document.querySelectorAll('.fi-sidebar-item a')].map(link => new URL(link.href).pathname));
    assert.deepEqual(links, [sections[permission]]);
    for (const [other, path] of Object.entries(sections)) {
      if (other !== permission) {
        assert.equal(await evaluate(async path => (await fetch(path + '/dodaj')).status, path), 403);
      }
    }
  };
  const find = (table, field, value) => sql('SELECT * FROM ' + table + ' WHERE ' + field + '=?', [value])[0];

  await browser.viewport(1440, 900);
  await employee('specializations');
  await html('/zespol/dodaj/', 404);
  await html('/specjalizacje/dodaj/', 404);
  await visit('/specjalizacje/dodaj');
  const title = 'Próbna specjalizacja';
  const slug = 'probna-specjalizacja';
  const specData = {
    title, slug, category: 'Testy', summary: 'Krótki opis testowy',
    body: [{ paragraph: 'Pierwszy akapit' }, { paragraph: 'Drugi akapit' }],
    scope: [{ entry: 'Punkt A' }, { entry: 'Punkt B' }],
  };
  const specialization = () => find('specializations', 'slug', slug);
  await wire('CreateSpecialization', 'create', { ...specData, slug: app.data.services[0].slug });
  assert.equal(sql('SELECT count(*) AS total FROM specializations')[0].total, 13);
  await wire('CreateSpecialization', 'create', { ...specData, body: [['Niepoprawny akapit']] });
  assert.equal(specialization(), undefined);
  assert(await evaluate(() => document.querySelectorAll('.fi-fo-field-wrp-error-message').length > 0));
  await wire('CreateSpecialization', 'create', { ...specData, active: true, display_order: 999 });
  assert(specialization());
  const detail = '/specjalizacje/' + slug + '/';
  const specId = specialization().id;
  assert.equal(Number(specialization().active), 0);
  assert.equal(specialization().display_order, 14);
  await html(detail, 404);
  await visit('/specjalizacje/' + specId + '/edytuj');
  await capture('specialization-editor');
  await wire('EditSpecialization', 'save', { slug: 'zmieniony', home_summary: 'Opis tylko na stronie głównej',
    body: specData.body.toReversed(), scope: specData.scope.toReversed() });
  assert.deepEqual(JSON.parse(specialization().body), ['Drugi akapit', 'Pierwszy akapit']);
  assert.deepEqual(JSON.parse(specialization().scope), ['Punkt B', 'Punkt A']);
  await visit('/specjalizacje');
  await capture('specialization-list');
  await action('ListSpecializations', 'activate', specialization());
  const visibleSpec = await html(detail);
  assert(visibleSpec.indexOf('Drugi akapit') < visibleSpec.indexOf('Pierwszy akapit'));
  assert(visibleSpec.indexOf('Punkt B') < visibleSpec.indexOf('Punkt A'));
  assert((await html('/specjalizacje/')).includes(title));
  assert((await html('/sitemap.xml')).includes(detail));
  const specOrder = specialization().display_order;
  for (let move = 0; move < 6; move++) await action('ListSpecializations', 'moveUp', specialization());
  assert(specialization().display_order < specOrder);
  assert((await html('/')).includes('Opis tylko na stronie głównej'));
  await visit('/specjalizacje/' + specId + '/edytuj');
  await wire('EditSpecialization', 'save', { home_summary: null });
  assert((await html('/')).includes(specData.summary));
  await visit('/specjalizacje');
  await action('ListSpecializations', 'deactivate', specialization());
  await html(detail, 404);
  assert(!(await html('/sitemap.xml')).includes(detail));
  assert(!(await html('/')).includes(title));
  await action('ListSpecializations', 'activate', specialization());
  await action('ListSpecializations', 'delete', specialization());
  await html(detail, 404);
  await action('ListSpecializations', 'restore', specialization());
  assert.equal(specialization().deleted_at, null);
  assert.equal(Number(specialization().active), 0);
  await html(detail, 404);

  await employee('team');
  const name = 'Próbny profil zespołu';
  const profile = () => find('team_members', 'name', name);
  await visit('/zespol/dodaj');
  const teamData = { name, role: 'Radca prawny', team_group: 'support', homepage_partner: true };
  await wire('CreateTeamMember', 'create', { ...teamData, portrait: '../office-reception-800.webp' });
  assert.equal(profile(), undefined);
  await wire('CreateTeamMember', 'create', { ...teamData, portrait: null });
  assert.equal(profile().team_group, 'support');
  assert.equal(profile().portrait, null);
  assert((await html('/zespol/')).includes(name));
  assert(!(await html('/')).includes(name));
  await visit('/zespol/' + profile().id + '/edytuj');
  await wire('EditTeamMember', 'save', { portrait, role: 'Radca prawny testowy' });
  assert.equal(profile().portrait, portrait);
  assert((await html('/zespol/')).includes('/assets/img/' + portrait));
  await visit('/zespol/' + profile().id + '/edytuj');
  await capture('team-editor');
  await wire('EditTeamMember', 'save', { portrait: null });
  assert.equal(profile().portrait, null);
  const before = sql('SELECT id, display_order FROM team_members WHERE team_group != ?', ['support']);
  await visit('/zespol');
  await capture('team-list');
  const order = profile().display_order;
  await action('ListTeamMembers', 'moveUp', profile());
  assert(profile().display_order < order);
  assert.deepEqual(sql('SELECT id, display_order FROM team_members WHERE team_group != ?', ['support']), before);
  await action('ListTeamMembers', 'delete', profile());
  assert(!(await html('/zespol/')).includes(name));
  await action('ListTeamMembers', 'restore', profile());
  assert((await html('/zespol/')).includes(name));
  const previousPartner = sql('SELECT id FROM team_members WHERE homepage_partner AND id != ? ORDER BY display_order LIMIT 1', [profile().id])[0];
  await visit('/zespol/' + previousPartner.id + '/edytuj');
  await wire('EditTeamMember', 'save', { homepage_partner: false });
  assert((await html('/')).includes(name));
  assert.equal(sql('SELECT count(*) AS count FROM users')[0].count, 4);

  await employee('career');
  const jobTitle = 'Próbna oferta pracy';
  const editedTitle = 'Próbna oferta po publikacji';
  const job = title => find('job_offers', 'title', title);
  const empty = await html('/kariera/');
  assert(empty.includes('career-opening') && !empty.includes(jobTitle));
  await visit('/kariera/dodaj');
  assert((await evaluate(() => document.body.innerText)).includes('Zapisz szkic'));
  await wire('CreateJobOffer', 'create', { title: jobTitle, url: 'javascript:alert(1)' });
  assert.equal(job(jobTitle), undefined);
  await wire('CreateJobOffer', 'create', { title: jobTitle, url: 'https://example.test/recruitment' });
  const jobId = job(jobTitle).id;
  assert.equal(job(jobTitle).status, 'draft');
  assert(!(await html('/kariera/')).includes(jobTitle));
  await visit('/kariera/' + jobId + '/edytuj');
  await capture('job-editor');
  await action('EditJobOffer', 'publish', null, { title: '' });
  assert.equal(job(jobTitle).status, 'draft');
  await action('EditJobOffer', 'publish', null, { title: editedTitle, description: '<b>Tekst próbny</b>' });
  assert.equal(job(editedTitle).status, 'open');
  assert((await html('/kariera/')).includes('&lt;b&gt;Tekst próbny&lt;/b&gt;'));
  await action('EditJobOffer', 'close', null);
  assert.equal(job(editedTitle).status, 'closed');
  assert.equal(await html('/kariera/'), empty);
  await visit('/kariera');
  await capture('job-list');
  await action('ListJobOffers', 'reopen', job(editedTitle));
  assert.equal(job(editedTitle).status, 'open');
  await visit('/kariera/dodaj');
  await wire('CreateJobOffer', 'create', { title: 'Druga oferta próbna' });
  await visit('/kariera');
  const secondJob = () => job('Druga oferta próbna');
  await action('ListJobOffers', 'publish', secondJob());
  const checkOrder = async first => {
    const content = await html('/kariera/');
    assert.equal(content.indexOf(editedTitle) < content.indexOf('Druga oferta próbna'), first);
  };
  await checkOrder(true);
  sql('UPDATE job_offers SET display_order=? WHERE id=?', [job(editedTitle).display_order, secondJob().id]);
  await visit('/kariera');
  await checkOrder(true);
  await action('ListJobOffers', 'moveUp', secondJob());
  await checkOrder(false);
  await action('ListJobOffers', 'delete', job(editedTitle));
  await action('ListJobOffers', 'restore', job(editedTitle));
  assert.equal(job(editedTitle).status, 'closed');
  assert(!(await html('/kariera/')).includes(editedTitle));
  await browser.viewport(390, 844, true);
  await visit('/kariera');
  await capture('mobile-recruitment');
  await evaluate(() => document.querySelector('.fi-topbar-open-sidebar-btn').click());
  await until(() => evaluate(() => {
    const sidebar = document.querySelector('.fi-sidebar').getBoundingClientRect();
    return sidebar.left >= -1 && sidebar.right >= 200;
  }), 'restricted navigation drawer');
  await capture('mobile-restricted-navigation');

  const existing = await evaluate(() => {
    const script = document.querySelector('script[data-update-uri]');
    return {
      path: new URL(script.getAttribute('data-update-uri'), location.href).pathname,
      token: script.getAttribute('data-csrf'),
      snapshot: [...document.querySelectorAll('[wire\\:snapshot]')].map(node => node.getAttribute('wire:snapshot'))
        .find(snapshot => JSON.parse(snapshot).memo.name.endsWith('\\ListJobOffers')),
    };
  });
  const cookies = (await browser.call('Network.getCookies', { urls: [app.adminOrigin] })).cookies
    .map(cookie => cookie.name + '=' + cookie.value).join('; ');
  await browser.viewport(1440, 900);
  await login(administrator, '/artykuly');
  const employeeId = find('users', 'email', 'career@example.test').id;
  await visit('/uzytkownicy/' + employeeId + '/edytuj');
  await wire('EditUser', 'save', { permissions: [] });
  const attempt = (origin) => new Promise((resolve, reject) => {
    const { host, port } = new URL(origin);
    const req = request({ hostname: '127.0.0.1', port, path: existing.path, method: 'POST', headers: {
      Host: host, Origin: app.adminOrigin, Cookie: cookies, 'X-Livewire': '', 'Content-Type': 'application/json',
    } }, response => {
      response.resume();
      resolve(response.statusCode);
    });
    req.on('error', reject);
    req.end(JSON.stringify({ _token: existing.token, components: [{
      snapshot: existing.snapshot, updates: {}, calls: [
        { path: '', method: 'mountAction', params: ['reopen', {}, { table: true, recordKey: String(jobId) }] },
      ],
    }] }));
  });
  assert.equal(await attempt(app.origin), 404);
  assert.equal(await attempt(app.adminOrigin), 403);
  assert.equal(job(editedTitle).status, 'closed');
});
