import { spawn } from 'node:child_process';
import { mkdir, mkdtemp, readFile, rm, writeFile } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { stopProcess, until } from './site.mjs';

export async function startBrowser() {
  const profile = await mkdtemp(join(tmpdir(), 'kancelaria-jp-chrome-'));
  const executable = process.env.CHROME_BIN || (process.platform === 'win32'
    ? join(process.env.ProgramFiles || 'C:\\Program Files', 'Google', 'Chrome', 'Application', 'chrome.exe')
    : process.platform === 'darwin'
      ? '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'
      : 'google-chrome');
  const child = spawn(executable, [
    '--headless=new', '--disable-extensions', '--no-first-run', '--disable-background-networking',
    '--no-sandbox', '--remote-debugging-port=0', '--remote-debugging-address=127.0.0.1',
    '--user-data-dir=' + profile, 'about:blank',
  ], { windowsHide: true, stdio: 'ignore' });
  let spawnError;
  let socket;
  child.on('error', error => { spawnError = error; });
  const stop = async () => {
    socket?.close();
    await stopProcess(child);
    await rm(profile, { recursive: true, force: true, maxRetries: 8, retryDelay: 100 });
  };

  try {
    const port = await until(async () => {
      if (spawnError) throw new Error('Cannot launch Chrome. Set CHROME_BIN to the executable.', { cause: spawnError });
      if (child.exitCode !== null) throw new Error('Chrome exited before opening its debugging socket.');
      return readFile(join(profile, 'DevToolsActivePort'), 'utf8').then(value => value.split('\n')[0], () => false);
    }, 'Chrome debugging port');
    const targets = await (await fetch('http://127.0.0.1:' + port + '/json/list')).json();
    socket = new WebSocket(targets.find(target => target.type === 'page').webSocketDebuggerUrl);
    await new Promise((resolve, reject) => {
      socket.addEventListener('open', resolve, { once: true });
      socket.addEventListener('error', reject, { once: true });
    });

    const pending = new Map();
    const events = new Map();
    let next = 0;
    socket.addEventListener('message', ({ data }) => {
      const message = JSON.parse(data);
      if (pending.has(message.id)) {
        const { resolve, reject, timer } = pending.get(message.id);
        pending.delete(message.id);
        clearTimeout(timer);
        if (message.error) reject(new Error(JSON.stringify(message.error)));
        else resolve(message.result);
      }
      for (const listener of events.get(message.method) ?? []) listener(message.params);
    });

    function call(method, params = {}) {
      const id = ++next;
      return new Promise((resolve, reject) => {
        const timer = setTimeout(() => {
          pending.delete(id);
          reject(new Error('Chrome did not respond to ' + method));
        }, 10000);
        pending.set(id, { resolve, reject, timer });
        socket.send(JSON.stringify({ id, method, params }));
      });
    }

    function on(event, listener) {
      if (!events.has(event)) events.set(event, new Set());
      events.get(event).add(listener);
      return () => events.get(event).delete(listener);
    }

    async function evaluate(fn, ...args) {
      const result = await call('Runtime.evaluate', {
        expression: '(' + fn + ')(...' + JSON.stringify(args) + ')',
        awaitPromise: true, returnByValue: true,
      });
      if (result.exceptionDetails) throw new Error(result.exceptionDetails.exception?.description || result.exceptionDetails.text);
      return result.result.value;
    }

    async function pageLoad(method, params) {
      let loaded = false;
      const unsubscribe = on('Page.loadEventFired', () => { loaded = true; });
      try {
        const result = await call(method, params);
        if (result.errorText) throw new Error(result.errorText);
        await until(() => loaded, 'document load');
        await evaluate(() => document.fonts.ready.then(() => true));
      } finally {
        unsubscribe();
      }
    }

    await call('Page.enable');
    await call('Runtime.enable');
    await call('Page.bringToFront');
    return {
      call, on, evaluate,
      navigate: url => pageLoad('Page.navigate', { url }),
      reload: () => pageLoad('Page.reload'),
      async viewport(width, height, mobile = false) {
        await call('Emulation.setDeviceMetricsOverride', { width, height, screenWidth: width, screenHeight: height, deviceScaleFactor: 1, mobile });
        await call('Emulation.setTouchEmulationEnabled', { enabled: mobile, maxTouchPoints: mobile ? 1 : undefined });
      },
      async key(key, vk) {
        for (const type of ['keyDown', 'keyUp']) {
          await call('Input.dispatchKeyEvent', {
            type, key, code: key, windowsVirtualKeyCode: vk, nativeVirtualKeyCode: vk,
            text: type === 'keyDown' && key === 'Enter' ? '\r' : undefined,
          });
        }
      },
      async screenshot(dir, name) {
        await mkdir(dir, { recursive: true });
        const { data } = await call('Page.captureScreenshot', { format: 'png' });
        await writeFile(join(dir, name + '.png'), Buffer.from(data, 'base64'));
      },
      async close() {
        await call('Browser.close').catch(() => {});
        await stop();
      },
    };
  } catch (error) {
    await stop();
    throw error;
  }
}
