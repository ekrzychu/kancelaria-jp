import { spawn } from 'node:child_process';
import { cp, mkdir, mkdtemp, readFile, rm } from 'node:fs/promises';
import { createServer } from 'node:net';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { setTimeout } from 'node:timers/promises';

const root = fileURLToPath(new URL('../../', import.meta.url));
export const php = process.env.PHP_BIN || 'php';

export async function until(condition, description, timeout = 7000) {
  const deadline = Date.now() + timeout;
  while (Date.now() < deadline) {
    const value = await condition();
    if (value) return value;
    await setTimeout(40);
  }
  throw new Error('Timed out waiting for ' + description);
}

export async function stopProcess(child) {
  if (!child.pid || child.exitCode !== null || child.signalCode !== null) return;
  const exited = new Promise(resolve => child.once('exit', resolve));
  child.kill();
  await exited;
}

async function freePort() {
  const socket = createServer();
  try {
    await new Promise((resolve, reject) => socket.once('error', reject).listen(0, '127.0.0.1', resolve));
    return socket.address().port;
  } finally {
    socket.close();
  }
}

export async function startSite() {
  const dir = await mkdtemp(join(tmpdir(), 'kancelaria-jp-test-'));
  let child;
  let log = '';
  let spawnError;
  const stop = async () => {
    if (child) await stopProcess(child);
    await rm(dir, { recursive: true, force: true, maxRetries: 4 });
  };

  try {
    await Promise.all(['app', 'public'].map(name => cp(join(root, name), join(dir, name), { recursive: true })));
    await mkdir(join(dir, 'sessions'));
    const host = '127.0.0.1:' + await freePort();
    const origin = 'http://' + host;
    child = spawn(php, [
      '-d', 'session.save_path=' + join(dir, 'sessions'),
      '-d', 'display_errors=stderr',
      '-S', host, '-t', 'public', 'public/router.php',
    ], { cwd: dir, stdio: ['ignore', 'ignore', 'pipe'], windowsHide: true, env: { ...process.env, PHP_CLI_SERVER_WORKERS: '' } });
    child.on('error', error => { spawnError = error; });
    child.stderr.setEncoding('utf8').on('data', chunk => { log = (log + chunk).slice(-12000); });
    await until(async () => {
      if (spawnError) throw spawnError;
      if (child.exitCode !== null) throw new Error(log);
      return fetch(origin + '/robots.txt', { method: 'HEAD' }).then(response => response.ok, () => false);
    }, 'isolated PHP server');

    const entries = await Promise.all(['site', 'services', 'team', 'articles', 'recruitment'].map(async name => [
      name, JSON.parse(await readFile(join(dir, 'app', 'data', name + '.json'), 'utf8')),
    ]));
    return { dir, origin, data: Object.fromEntries(entries), stop, log: () => log };
  } catch (error) {
    await stop();
    throw error;
  }
}

export function routes(data) {
  return [...new Set([
    '/', ...data.site.nav.map(item => item.url), '/dokumenty/',
    ...data.services.map(item => '/specjalizacje/' + item.slug + '/'),
    ...data.articles.map(item => '/' + item.slug + '/'),
    ...data.site.documents.map(item => '/dokumenty/' + item.slug + '/'),
  ])];
}
