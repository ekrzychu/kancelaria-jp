import { execFileSync } from 'node:child_process';
import { randomBytes } from 'node:crypto';
import { fileURLToPath } from 'node:url';

const bridge = fileURLToPath(new URL('./mariadb.php', import.meta.url));

export function createTestDatabase(php) {
  const host = process.env.TEST_DB_HOST;
  const port = process.env.TEST_DB_PORT;
  const username = process.env.TEST_DB_USERNAME;
  const password = process.env.TEST_DB_PASSWORD;
  if (host !== '127.0.0.1' || !/^\d+$/.test(port ?? '') || Number(port) === 3306 ||
      Number(port) < 1 || Number(port) > 65535 || username !== 'kjp_test' || !password) {
    throw new Error('Set the dedicated TEST_DB_* credentials and a loopback port other than 3306. See README.');
  }

  const schema = 'kancelaria_jp_test_' + randomBytes(8).toString('hex');
  const run = (operation, input) => execFileSync(php, [bridge, operation, schema], {
    input, encoding: 'utf8', windowsHide: true, maxBuffer: 8 * 1024 * 1024,
  });
  const version = run('create').trim();

  return {
    version,
    schema,
    env: {
      DB_CONNECTION: 'mariadb',
      DB_HOST: host,
      DB_PORT: port,
      DB_DATABASE: schema,
      DB_USERNAME: username,
      DB_PASSWORD: password,
      DB_SOCKET: '',
      DB_URL: '',
      MYSQL_ATTR_SSL_CA: '',
    },
    query(operations) {
      return JSON.parse(run('query', JSON.stringify(operations)));
    },
    drop() {
      run('drop');
    },
  };
}
