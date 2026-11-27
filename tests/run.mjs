import { execFileSync } from 'node:child_process';

const php = process.env.PHP_BIN || 'php';
for (const [tool, args] of [
  [process.execPath, ['tests/check.mjs']],
  [php, ['tests/content.php']],
  [process.execPath, ['--test', 'tests/database.test.mjs']],
  [process.execPath, ['--test', 'tests/http.test.mjs']],
  [process.execPath, ['--test', 'tests/browser.test.mjs']],
  [process.execPath, ['--test', 'tests/panel.test.mjs']],
  [process.execPath, ['--test', 'tests/content-admin.test.mjs']],
  [process.execPath, ['--test', 'tests/media.test.mjs']],
  [process.execPath, ['--test', 'tests/contact.test.mjs']],
  [process.execPath, ['--test', 'tests/analytics.test.mjs']],
]) {
  const github = process.env.GITHUB_ACTIONS === 'true';
  if (github) console.log('::group::' + args.at(-1));
  try {
    execFileSync(tool, args, { stdio: 'inherit' });
  } finally {
    if (github) console.log('::endgroup::');
  }
}
