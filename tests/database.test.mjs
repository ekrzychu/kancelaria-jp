import assert from 'node:assert/strict';
import { readFile, writeFile } from 'node:fs/promises';
import { join } from 'node:path';
import { test } from 'node:test';
import { startSite } from './support/site.mjs';

const tables = ['articles', 'specializations', 'team_members', 'job_offers'];
const counts = database => database.query(tables.map(table => ({ sql: 'SELECT COUNT(*) AS total FROM ' + table })))
  .map(result => Number(result[0].total));
const readDataset = async (dir, name) => JSON.parse(await readFile(join(dir, 'app', 'data', name + '.json'), 'utf8'));
const writeDataset = (dir, name, data) => writeFile(join(dir, 'app', 'data', name + '.json'), JSON.stringify(data));
const rows = (database, sql, params = []) => database.query([{ sql, params }])[0];
const importCommand = ['site:import-content', '--no-ansi'];

function failedImport(artisan, args, diagnostic) {
  assert.throws(() => artisan(args), error => {
    assert.equal(error.status, 1);
    assert.match(error.stdout, diagnostic);
    return true;
  });
}

test('MariaDB migrations, dry run, rollback, exact import, and repeat refusal', async t => {
  const { dir, database, artisan, modelChecks, stop } = await startSite({ importContent: false, serve: false });
  t.after(stop);

  assert.match(database.version, /^(?:11\.4|12\.3)\.\d+.*MariaDB/i);
  assert.deepEqual(counts(database), [0, 0, 0, 0]);
  const tableSettings = rows(database,
    'SELECT TABLE_NAME, ENGINE, TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() ORDER BY TABLE_NAME');
  assert.deepEqual(tableSettings.map(table => table.TABLE_NAME), [
    ...tables, 'migrations', 'admin_account_lock', 'users', 'password_reset_tokens', 'sessions', 'media_images', 'contact_messages',
  ].sort());
  assert(tableSettings.every(table => table.ENGINE === 'InnoDB' && table.TABLE_COLLATION === 'utf8mb4_unicode_ci'));
  const date = rows(database, "SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='articles' AND COLUMN_NAME='date'");
  assert.equal(date[0].DATA_TYPE, 'date');

  const dry = artisan([...importCommand, '--dry-run'], { DB_PORT: '1' });
  assert.match(dry, /Dry run complete\. No database writes/);
  for (const [table, count] of Object.entries({ articles: 3, specializations: 13, team_members: 23, job_offers: 0 })) {
    assert.match(dry, new RegExp('\\| '+table+'\\s+\\| '+count+'\\s+\\|'));
  }
  assert.deepEqual(counts(database), [0, 0, 0, 0]);

  const source = await readDataset(dir, 'articles');
  for (const [change, diagnostic] of [
    [{ slug: 'admin' }, /non-reserved slug/],
    [{ slug: 'invalid\/url' }, /lowercase route slug/],
    [{ date: '2026-02-29' }, /valid YYYY-MM-DD date/],
    [{ date: '2026-1-01' }, /valid YYYY-MM-DD date/],
    [{ date: 20260101 }, /valid YYYY-MM-DD date/],
    [{ title: 'ą'.repeat(256) }, /at most 255 characters/],
  ]) {
    await writeDataset(dir, 'articles', [{ ...source[0], ...change }, ...source.slice(1)]);
    failedImport(artisan, importCommand, diagnostic);
    assert.deepEqual(counts(database), [0, 0, 0, 0]);
  }
  await writeDataset(dir, 'articles', source);

  database.query([{
    sql: "CREATE TRIGGER test_reject_team BEFORE INSERT ON team_members FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Synthetic rollback check'",
  }]);
  failedImport(artisan, importCommand, /MariaDB import failed/);
  assert.deepEqual(counts(database), [0, 0, 0, 0]);
  database.query([{ sql: 'DROP TRIGGER test_reject_team' }]);

  assert.match(artisan(importCommand), /Content imported/);
  assert.deepEqual(counts(database), [3, 13, 23, 0]);
  const articles = rows(database, 'SELECT slug,title,summary,category,author,body_html,date,status,image,image_alt FROM articles ORDER BY id');
  assert.deepEqual(articles, source.map(entry => Object.fromEntries(Object.keys(articles[0]).map(key =>
    [key, entry[key] ?? (key === 'status' ? 'published' : null)]))));
  const services = rows(database, 'SELECT slug,title,category,summary,home_summary,body,scope,active,display_order FROM specializations ORDER BY display_order,id');
  const sourceServices = await readDataset(dir, 'services');
  assert.deepEqual(services.map(row => ({
    ...row, active: Boolean(Number(row.active)), display_order: Number(row.display_order),
    body: JSON.parse(row.body), scope: JSON.parse(row.scope),
  })), sourceServices.map((entry, index) => ({ home_summary: null, ...entry, active: true, display_order: index + 1 })));

  const team = rows(database, 'SELECT name,role,portrait,team_group,homepage_partner,display_order FROM team_members ORDER BY display_order,id');
  const sourceTeam = await readDataset(dir, 'team');
  const approvedGroups = [
    ...Array(4).fill('leadership'), ...Array(3).fill('coordinators'), ...Array(11).fill('lawyers'), ...Array(5).fill('support'),
  ];
  assert.deepEqual(team.map(row => ({
    ...row, homepage_partner: Boolean(Number(row.homepage_partner)), display_order: Number(row.display_order),
  })), sourceTeam.map((entry, index) => ({
    portrait: null, ...entry, team_group: approvedGroups[index], homepage_partner: index < 2, display_order: index + 1,
  })));

  assert.match(modelChecks(), /Model constraints, slug stability and soft deletion passed/);
  const edit = 'Zachowaj poprawiony tytuł';
  database.query([
    { sql: 'UPDATE articles SET title=? WHERE slug=?', params: [edit, articles[0].slug] },
    ...tables.map(table => ({ sql: 'UPDATE ' + table + ' SET deleted_at=?', params: ['2026-11-01 01:00:00'] })),
  ]);
  failedImport(artisan, importCommand, /already populated, including soft-deleted records/);
  assert.deepEqual(counts(database).slice(1), [13, 23, 0]);
  assert.equal(rows(database, 'SELECT title FROM articles WHERE slug=?', [articles[0].slug])[0].title, edit);
});

test('import preserves long Unicode HTML, article states and optional job fields', async t => {
  const { dir, database, artisan, stop } = await startSite({ importContent: false, serve: false });
  t.after(stop);
  const html = '<h2>Zażółć gęślą jaźń 😀</h2><p>' + 'Łódź 漢字 😀 '.repeat(6500) +
    '</p><figure><img src="/assets/img/podzial-majatku-1.png" alt="Próba"><figcaption>Opis</figcaption></figure>' +
    '<ol><li><a href="/kontakt/">Odnośnik</a></li></ol><table><caption>Próba</caption><tr><td>żółć</td></tr></table>';
  assert(Buffer.byteLength(html, 'utf8') > 65535);
  const original = [
    { slug: 'unicode-draft', title: 'Dokument Łódź – 漢字 😀', body_html: html, status: 'draft', date: '2024-02-29' },
    { slug: 'withdrawn-without-date', title: 'Wycofana próba', body_html: '<p>Próba</p>', status: 'unpublished' },
  ];
  const vacancies = [
    { title: 'Otwarte stanowisko próbne', status: 'open', description: 'Łódź 😀', url: '/kontakt/' },
    { status: 'closed' }, { status: 'draft' },
  ];
  await writeDataset(dir, 'articles', original);
  await writeDataset(dir, 'recruitment', { vacancies });
  assert.match(artisan(importCommand), /Content imported/);
  assert.deepEqual(counts(database), [2, 13, 23, 3]);

  const stored = rows(database, 'SELECT slug,title,status,date,author,summary,body_html FROM articles ORDER BY id');
  assert.deepEqual(stored, original.map(article => ({ date: null, author: null, summary: null, ...article })));
  const jobs = rows(database, 'SELECT title,description,url,status,display_order FROM job_offers ORDER BY display_order,id');
  assert.deepEqual(jobs, vacancies.map((job, index) => ({ title: null, description: null, url: null, ...job, display_order: index + 1 })));
});
