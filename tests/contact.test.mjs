import assert from 'node:assert/strict';
import { randomBytes } from 'node:crypto';
import { readFile } from 'node:fs/promises';
import { join } from 'node:path';
import { test } from 'node:test';
import { startBrowser } from './support/chrome.mjs';
import { startSite, until } from './support/site.mjs';

test('contact intake, confidential administration and cron delivery', async t => {
  const app = await startSite({ cached: true });
  t.after(app.stop);
  const sql = (query, params = []) => app.database.query([{ sql: query, params }])[0];
  const submission = {
    name: 'Zażółć Gęślą',
    email: 'visitor@example.test',
    phone: '+48 555 010 444',
    message: 'Pierwszy wiersz\n<script>contactFixture()</script>\nOstatni wiersz\n',
  };
  const records = () => sql('SELECT * FROM contact_messages ORDER BY created_at,id');
  const page = await fetch(app.origin + '/kontakt/');
  assert.equal(page.status, 200);
  const cookie = page.headers.getSetCookie().map(header => header.split(';')[0]).join('; ');
  const token = (await page.text()).match(/name="_token" value="([^"]+)"/)[1];
  const post = (data, csrf = token) => fetch(app.origin + '/kontakt/', {
    method: 'POST', headers: { cookie }, redirect: 'manual',
    body: new URLSearchParams({ ...data, _token: csrf }),
  });
  const state = () => sql('SELECT * FROM contact_messages WHERE id=?', [id])[0];
  let id;

  await t.test('CSRF, validation, honeypot, confirmation and rate limit', async () => {
    const expired = await post(submission, 'invalid');
    assert.equal(expired.status, 419);
    assert.match(await expired.text(), /Sesja formularza wygasła/);

    const missing = await post({});
    assert.equal(missing.status, 422);
    assert.match(await missing.text(), /Uzupełnij pole „Opis sprawy”/);

    const invalid = await post({ ...submission, email: 'visitor@example.test\r\nBcc: leak@example.test' });
    assert.equal(invalid.status, 422);
    const invalidHtml = await invalid.text();
    assert.match(invalidHtml, /Podaj poprawny adres e-mail/);
    assert.match(invalidHtml, /&lt;script&gt;contactFixture\(\)&lt;\/script&gt;/);
    assert.doesNotMatch(invalidHtml, /<script>contactFixture/);

    const long = await post({ ...submission, message: 'ą'.repeat(4001) });
    assert.equal(long.status, 422);
    assert.match(await long.text(), /najwyżej 4000 znaków/);
    assert.equal((await post({ ...submission, website: 'https://example.test/' })).status, 422);
    assert.deepEqual(records(), []);

    const accepted = await post(submission);
    assert.equal(accepted.status, 303);
    assert.equal(accepted.headers.get('location'), app.origin + '/kontakt/#formularz');
    assert.match(accepted.headers.get('cache-control'), /no-store/);
    const confirmation = await fetch(accepted.headers.get('location'), { headers: { cookie } });
    const html = await confirmation.text();
    assert.match(html, /Wiadomość została przyjęta/);
    assert.doesNotMatch(html, /visitor@example\.test|contactFixture/);
    assert.doesNotMatch(await (await fetch(app.origin + '/kontakt/?wyslano=1')).text(), /Wiadomość została przyjęta/);

    const [record] = records();
    id = record.id;
    for (const [field, value] of Object.entries(submission)) assert.equal(record[field], value);
    assert.equal(record.status, 'unread');
    assert.equal(record.delivery_status, 'pending');
    assert.equal(record.delivery_attempts, 0);

    const throttled = await post(submission);
    assert.equal(throttled.status, 429);
    assert(Number(throttled.headers.get('retry-after')) > 0);
    assert.match(await throttled.text(), /Zbyt wiele prób/);
    assert.equal(records().length, 1);
  });

  const deliver = mode => JSON.parse(app.panelChecks(['contact-delivery', mode]));
  const due = () => sql('UPDATE contact_messages SET next_attempt_at=UTC_TIMESTAMP() WHERE id=?', [id]);
  let messageId;
  await t.test('mail failure preserves the message and bounds automatic retries', () => {
    const first = deliver('fail');
    assert.equal(first.exit, 0);
    assert.equal(first.delivered, 0);
    assert.match(first.output, /failed: 1/);
    assert.doesNotMatch(first.output, /confidential|visitor@example\.test/);
    assert.equal(first.attempts.length, 1);
    const [mail] = first.attempts;
    messageId = mail.message_id;
    assert.equal(messageId, id + '@127.0.0.1');
    assert.deepEqual(mail.to, ['kontakt@kancelaria-jp.pl']);
    assert.deepEqual(mail.from, ['contact@example.test']);
    assert.deepEqual(mail.reply_to, [submission.email]);
    assert.deepEqual(mail.cc, []);
    assert.deepEqual(mail.bcc, []);
    assert.equal(mail.html, null);
    assert.equal(mail.body, 'Imię i nazwisko: ' + submission.name + '\nAdres e-mail: ' + submission.email
      + '\nTelefon: ' + submission.phone + '\n\nTreść wiadomości:\n' + submission.message);
    assert.equal(state().delivery_status, 'failed');
    assert.equal(state().message, submission.message);
    assert(state().next_attempt_at);
    assert.deepEqual(deliver('fail').attempts, []);

    for (const attempt of [2, 3]) {
      due();
      assert.equal(deliver('fail').attempts[0].message_id, messageId);
      assert.equal(state().delivery_attempts, attempt);
    }
    assert.equal(state().next_attempt_at, null);
    assert.deepEqual(deliver('send').attempts, []);
  });

  await t.test('Wiadomości enforces permissions and exposes only status actions', async () => {
    const admin = 'admin@example.test';
    const reader = 'messages@example.test';
    const denied = 'articles@example.test';
    const password = 'Aa!' + randomBytes(18).toString('hex');
    app.panelChecks(['provision'], JSON.stringify({ name: 'Test admin', email: admin, password }));
    for (const [email, section] of [[reader, 'messages'], [denied, 'articles']]) {
      sql('INSERT INTO users (name,email,password,permissions) SELECT ?,?,password,? FROM users WHERE email=?',
        [section, email, JSON.stringify([section]), admin]);
    }
    const path = '/wiadomosci/' + id;
    assert.equal((await fetch(app.origin + path)).status, 404);

    const browser = await startBrowser();
    t.after(browser.close);
    const { evaluate } = browser;
    const visit = path => browser.navigate(app.adminOrigin + path);
    const wire = async (name, method, data = {}, args = []) => {
      await until(() => evaluate(() => !!window.Livewire), 'Livewire');
      return evaluate(async (name, method, data, args) => {
        const element = [...document.querySelectorAll('[wire\\:snapshot]')]
          .find(el => JSON.parse(el.getAttribute('wire:snapshot')).memo.name.split('\\').at(-1) === name);
        const component = window.Livewire.find(element.getAttribute('wire:id'));
        for (const [key, value] of Object.entries(data)) await component.$set('data.' + key, value, false);
        await component.$call(method, ...args);
      }, name, method, data, args);
    };
    const waitForPath = path => until(() => evaluate(path => location.pathname === path, path), path);
    const login = async (email, path) => {
      await browser.call('Network.clearBrowserCookies');
      await visit('/login');
      await wire('Login', 'authenticate', { email, password });
      await waitForPath(path);
    };
    const action = async name => {
      await wire('ViewContactMessage', 'mountAction', {}, [name]);
      await wire('ViewContactMessage', 'callMountedAction');
    };
    const status = path => evaluate(async path => (await fetch(path, { redirect: 'manual' })).status, path);
    await login(denied, '/artykuly');
    assert.equal(await status('/wiadomosci'), 403);
    assert.equal(await status(path), 403);
    assert.equal(state().status, 'unread');

    await login(reader, '/wiadomosci');
    assert.equal(await status('/wiadomosci'), 200);
    await until(() => evaluate(() => window.Livewire && document.querySelector('input[type=search]') !== null), 'messages search field');
    assert(await evaluate(email => document.body.textContent.includes(email), submission.email));
    await evaluate(async () => {
      const element = document.querySelector('input[type=search]').closest('[wire\\:id]');
      await window.Livewire.find(element.getAttribute('wire:id')).$set('tableSearch', 'no-match@example.test');
    });
    await until(() => evaluate(email => !document.body.textContent.includes(email), submission.email), 'filtered messages');
    assert.equal(await evaluate(() => location.search), '');
    await visit(path);
    assert.equal(state().status, 'read');
    assert(await evaluate(() => document.body.textContent.includes('<script>contactFixture()</script>')));
    assert(await evaluate(() => ![...document.scripts].some(script => script.textContent.includes('contactFixture()'))));
    assert.equal(await status(path + '/edytuj'), 404);
    await wire('ViewContactMessage', 'mountAction', {}, ['markUnread']);
    await waitForPath('/wiadomosci');
    assert.equal(state().status, 'unread');

    await visit(path);
    await action('archive');
    assert.equal(state().status, 'archived');
    assert(state().archived_at);

    await wire('ViewContactMessage', 'mountAction', {}, ['retryDelivery']);
    sql("UPDATE users SET permissions='[]' WHERE email=?", [reader]);
    await browser.call('Network.enable');
    let actionStatus;
    const unsubscribe = browser.on('Network.responseReceived', ({ response }) => {
      if (new URL(response.url).pathname.endsWith('/update')) actionStatus = response.status;
    });
    await assert.rejects(wire('ViewContactMessage', 'callMountedAction'));
    await until(() => actionStatus, 'denied Livewire update');
    unsubscribe();
    assert.equal(actionStatus, 403);
    assert.equal(state().next_attempt_at, null);

    sql('UPDATE users SET permissions=? WHERE email=?', [JSON.stringify(['messages']), reader]);
    await visit(path);
    await action('retryDelivery');
    assert(state().next_attempt_at);
    assert.equal(state().delivery_attempts, 3);
  });

  await t.test('manual retry delivers once without unarchiving and interrupted claims are held for review', async () => {
    const result = deliver('send');
    assert.equal(result.delivered, 1);
    assert.equal(result.attempts[0].message_id, messageId);
    assert.equal(state().delivery_status, 'sent');
    assert.equal(state().delivery_attempts, 4);
    assert.equal(state().status, 'archived');
    assert(state().sent_at);
    assert.deepEqual(deliver('send').attempts, []);

    sql("INSERT INTO contact_messages (id,name,email,message,delivery_status,delivery_attempts,last_attempt_at) "
      + "VALUES (UUID(),'Interrupted fixture','interrupted@example.test','Synthetic','sending',1,UTC_TIMESTAMP() - INTERVAL 31 MINUTE)");
    assert.deepEqual(deliver('send').attempts, []);
    assert.equal(sql('SELECT delivery_status FROM contact_messages WHERE email=?', ['interrupted@example.test'])[0].delivery_status, 'uncertain');
    const logs = await readFile(join(app.dir, 'storage', 'logs', 'laravel.log'), 'utf8').catch(error => {
      if (error.code === 'ENOENT') return '';
      throw error;
    });
    assert.doesNotMatch(logs, /Synthetic confidential transport failure|visitor@example\.test|contactFixture/);
  });
});
