import { execFile, execFileSync, spawn } from 'node:child_process';
import { randomBytes } from 'node:crypto';
import { cp, mkdir, mkdtemp, readFile, rm, symlink } from 'node:fs/promises';
import { createServer } from 'node:net';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { setTimeout } from 'node:timers/promises';
import { promisify } from 'node:util';

import { createTestDatabase } from './database.mjs';

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

export async function startSite({ importContent = true, serve = true, cached = false, prepare } = {}) {
  const dir = await mkdtemp(join(tmpdir(), 'kancelaria-jp-test-'));
  let child;
  let database;
  let log = '';
  let spawnError;
  const stop = async () => {
    try {
      if (child) await stopProcess(child);
      database?.drop();
      database = undefined;
    } finally {
      await rm(dir, { recursive: true, force: true, maxRetries: 4 });
    }
  };

  try {
    const content = join(dir, 'app', 'data');
    const storage = join(dir, 'storage');
    const publicDir = join(dir, 'public');
    const router = join(root, 'vendor', 'laravel', 'framework', 'src', 'Illuminate', 'Foundation', 'resources', 'server.php');
    await Promise.all([
      mkdir(join(storage, 'framework', 'views'), { recursive: true }),
      mkdir(join(storage, 'logs'), { recursive: true }),
      mkdir(join(dir, 'bootstrap', 'cache'), { recursive: true }),
      symlink(join(root, 'vendor'), join(dir, 'vendor'), process.platform === 'win32' ? 'junction' : 'dir'),
    ]);
    await Promise.all(['app', 'artisan', 'bootstrap/app.php', 'bootstrap/providers.php', 'composer.json', 'config', 'database', 'public', 'resources', 'routes'].map(name =>
      cp(join(root, name), join(dir, name), { recursive: true })));
    await prepare?.(dir);
    database = createTestDatabase(php);
    const host = '127.0.0.1:' + await freePort();
    const origin = 'http://' + host;
    const env = { ...process.env };
    for (const name of ['APP_CONFIG_CACHE', 'APP_EVENTS_CACHE', 'APP_PACKAGES_CACHE', 'APP_ROUTES_CACHE', 'APP_SERVICES_CACHE', 'PHP_CLI_SERVER_WORKERS']) {
      delete env[name];
    }
    const runtimeEnv = {
      ...env,
      APP_ENV: 'testing',
      APP_DEBUG: 'false',
      APP_URL: origin,
      PUBLIC_HOST: '127.0.0.1',
      ADMIN_HOST: 'panel.localhost',
      ADMIN_URL: origin.replace('127.0.0.1', 'panel.localhost'),
      ADMIN_TEST_RECOVERY: 'true',
      APP_KEY: 'base64:' + randomBytes(32).toString('base64'),
      APP_MAINTENANCE_DRIVER: 'file',
      LARAVEL_STORAGE_PATH: storage,
      VIEW_COMPILED_PATH: join(storage, 'framework', 'views'),
      SITE_CONTENT_PATH: content,
      CACHE_STORE: 'array',
      LOG_CHANNEL: 'single',
      MAIL_MAILER: 'array',
      QUEUE_CONNECTION: 'sync',
      ...database.env,
    };
    const artisan = (args, overrides = {}) => execFileSync(php, ['artisan', ...args], {
      cwd: dir, env: { ...runtimeEnv, ...overrides }, encoding: 'utf8', windowsHide: true,
    });
    const panelChecks = (args, input) => execFileSync(php, [join(root, 'tests', 'support', 'panel-checks.php'), dir, ...args], {
      cwd: dir, env: runtimeEnv, input, encoding: 'utf8', windowsHide: true,
    });
    const concurrentPanelCheck = args => promisify(execFile)(
      php, [join(root, 'tests', 'support', 'panel-checks.php'), dir, ...args],
      { cwd: dir, env: runtimeEnv, encoding: 'utf8', windowsHide: true },
    );
    const modelChecks = () => execFileSync(php, [join(root, 'tests', 'support', 'model-checks.php'), dir], {
      cwd: dir, env: runtimeEnv, encoding: 'utf8', windowsHide: true,
    });
    artisan(['migrate', '--force', '--no-ansi']);
    if (importContent) artisan(['site:import-content', '--no-ansi']);
    if (cached) {
      artisan(['config:cache', '--no-ansi']);
      artisan(['route:cache', '--no-ansi']);
    }
    if (!serve) return { dir, database, artisan, modelChecks, stop };

    child = spawn(php, [
      '-d', 'display_errors=stderr', '-S', host, '-t', publicDir, router,
    ], {
      cwd: publicDir,
      stdio: ['ignore', 'ignore', 'pipe'],
      windowsHide: true,
      env: runtimeEnv,
    });
    child.on('error', error => { spawnError = error; });
    child.stderr.setEncoding('utf8').on('data', chunk => { log = (log + chunk).slice(-12000); });
    await until(async () => {
      if (spawnError) throw spawnError;
      if (child.exitCode !== null) throw new Error(log);
      const response = await fetch(origin + '/').catch(() => null);
      if (!response) return false;
      if (!response.ok || !(await response.text()).includes('<main id="main"')) {
        const runtimeLog = await readFile(join(storage, 'logs', 'laravel.log'), 'utf8').catch(() => '');
        const cause = runtimeLog.split(/\r?\n/).find(line => line.includes('.ERROR:'));
        throw new Error('Laravel did not render the homepage (HTTP ' + response.status + ')\n' + log + (cause || ''));
      }
      return true;
    }, 'isolated Laravel server');

    const entries = await Promise.all(['site', 'services', 'team', 'articles', 'recruitment'].map(async name => [
      name, JSON.parse(await readFile(join(content, name + '.json'), 'utf8')),
    ]));
    return { dir, origin, adminOrigin: runtimeEnv.ADMIN_URL, data: Object.fromEntries(entries), database, artisan,
      panelChecks, concurrentPanelCheck, stop, log: () => log };
  } catch (error) {
    await stop();
    throw error;
  }
}

export function routes(data) {
  return [...new Set([
    '/', ...data.site.nav.map(item => item.url), '/dokumenty/',
    ...data.services.map(item => '/specjalizacje/' + item.slug + '/'),
    ...data.articles.filter(item => !Object.hasOwn(item, 'status') || item.status === 'published')
      .map(item => '/' + item.slug + '/'),
    ...data.site.documents.map(item => '/dokumenty/' + item.slug + '/'),
  ])];
}
