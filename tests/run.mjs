import { execFileSync } from 'node:child_process';

const php = process.env.PHP_BIN || 'php';
for (const [tool, args] of [
  [process.execPath, ['tests/check.mjs']],
  [php, ['tests/content.php']],
  [process.execPath, ['--test', 'tests/http.test.mjs']],
  [process.execPath, ['--test', 'tests/browser.test.mjs']],
]) execFileSync(tool, args, { stdio: 'inherit', env: process.env });
