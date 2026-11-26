import { execFileSync } from 'node:child_process';
import { existsSync } from 'node:fs';
import { fileURLToPath } from 'node:url';

process.chdir(fileURLToPath(new URL('../', import.meta.url)));
const php = process.env.PHP_BIN || 'php';
const paths = execFileSync('git', ['ls-files', '-z', '--cached', '--others', '--exclude-standard', '--', '*.php', 'artisan'], { encoding: 'utf8' }).split('\0').filter(path => path && existsSync(path));
for (const path of new Set(paths)) execFileSync(php, ['-l', path], { stdio: 'inherit' });
execFileSync(process.execPath, ['--check', 'public/assets/js/main.js'], { stdio: 'inherit' });
console.log('JavaScript syntax passed.');
